/**
 * UCRD Management System - Form Validation Functions
 * Contains validation functions for forms across the system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Apply validation to forms with the 'needs-validation' class
    const forms = document.querySelectorAll('.needs-validation');
    
    if (forms.length > 0) {
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // Add ORCID validation for inputs with orcid-input class
    const orcidInputs = document.querySelectorAll('.orcid-input');
    if (orcidInputs.length > 0) {
        Array.from(orcidInputs).forEach(input => {
            input.addEventListener('blur', validateORCID);
            input.addEventListener('input', function() {
                // Auto-format ORCID as user types
                let value = this.value.replace(/[^0-9X]/gi, '');
                if (value.length > 4) {
                    value = value.substr(0, 4) + '-' + value.substr(4);
                }
                if (value.length > 9) {
                    value = value.substr(0, 9) + '-' + value.substr(9);
                }
                if (value.length > 14) {
                    value = value.substr(0, 14) + '-' + value.substr(14, 4);
                }
                // Limit to correct ORCID length (with dashes)
                if (value.length > 19) {
                    value = value.substr(0, 19);
                }
                this.value = value;
            });
        });
    }
    
    // Automatically populate supervisor info based on selection
    const supervisorSelects = document.querySelectorAll('.supervisor-select');
    if (supervisorSelects.length > 0) {
        Array.from(supervisorSelects).forEach(select => {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const supervisorData = JSON.parse(selectedOption.getAttribute('data-supervisor') || '{}');
                
                // Populate fields if they exist
                const departmentField = document.getElementById('supervisor_department');
                const emailField = document.getElementById('supervisor_email');
                const phoneField = document.getElementById('supervisor_phone');
                
                if (departmentField) departmentField.value = supervisorData.department || '';
                if (emailField) emailField.value = supervisorData.email || '';
                if (phoneField) phoneField.value = supervisorData.phone || '';
            });
        });
    }
    
    // Date range validation
    const startDateInputs = document.querySelectorAll('.start-date');
    const endDateInputs = document.querySelectorAll('.end-date');
    
    if (startDateInputs.length > 0 && endDateInputs.length > 0) {
        Array.from(startDateInputs).forEach((startInput, index) => {
            if (endDateInputs[index]) {
                const endInput = endDateInputs[index];
                
                // Set min date on end date based on start date
                startInput.addEventListener('change', function() {
                    endInput.min = this.value;
                    
                    // Validate end date if it's already filled
                    if (endInput.value && endInput.value < this.value) {
                        endInput.setCustomValidity('End date must be after start date');
                    } else {
                        endInput.setCustomValidity('');
                    }
                });
                
                // Validate when end date changes
                endInput.addEventListener('change', function() {
                    if (startInput.value && this.value < startInput.value) {
                        this.setCustomValidity('End date must be after start date');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    }
});

/**
 * Validates an ORCID identifier
 * ORCID format: 0000-0000-0000-000X (where X can be 0-9 or X)
 */
function validateORCID() {
    const orcidRegex = /^(\d{4}-){3}\d{3}[\dX]$/;
    const value = this.value.trim();
    
    // Skip validation if empty (assuming it's not required)
    if (!value) {
        this.setCustomValidity('');
        return true;
    }
    
    if (!orcidRegex.test(value)) {
        this.setCustomValidity('Please enter a valid ORCID in format: 0000-0000-0000-000X');
        return false;
    }
    
    // Checksum validation - advanced ORCID validation
    // See: https://support.orcid.org/hc/en-us/articles/360006897674-Structure-of-the-ORCID-Identifier
    const digits = value.replace(/-/g, '').split('');
    const lastDigit = digits.pop();
    let total = 0;
    
    for (let i = 0; i < digits.length; i++) {
        total = (total + parseInt(digits[i])) * 2;
    }
    
    const remainder = total % 11;
    const result = (12 - remainder) % 11;
    
    const checkDigit = result === 10 ? 'X' : result.toString();
    
    if (checkDigit !== lastDigit.toUpperCase()) {
        this.setCustomValidity('Invalid ORCID checksum - please verify your ORCID');
        return false;
    }
    
    this.setCustomValidity('');
    return true;
} 