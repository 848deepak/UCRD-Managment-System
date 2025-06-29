    </div> <!-- End of container -->
    
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> UCRD Management System</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    // Common JavaScript functions
    
    // Enable submit button only when required fields are filled
    $(document).ready(function() {
        // Automatically dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Confirm delete operations
        $('.delete-btn').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html> 