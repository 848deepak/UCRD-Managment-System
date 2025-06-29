-- Sample data for UCRD Management System

-- Sample Supervisors
INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
('Dr. Rajesh Kumar', 'Computer Science', 'rkumar@university.edu', '555-123-4567', '0000-0002-1825-0097'),
('Prof. Priya Sharma', 'Electrical Engineering', 'psharma@university.edu', '555-234-5678', '0000-0001-5432-1234'),
('Dr. Vikram Patel', 'Data Science', 'vpatel@university.edu', '555-345-6789', '0000-0003-9876-5432'),
('Prof. Neha Mehta', 'Artificial Intelligence', 'nmehta@university.edu', '555-456-7890', '0000-0002-6543-2109'),
('Dr. Arjun Singh', 'Cybersecurity', 'asingh@university.edu', '555-567-8901', '0000-0001-8765-4321');

-- Sample Researchers
INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
('Amit Verma', 'averma@university.edu', '555-987-6543', 'Computer Science', 2021, 1, '0000-0001-2345-6789'),
('Divya Gupta', 'dgupta@university.edu', '555-876-5432', 'Electrical Engineering', 2022, 2, '0000-0002-3456-7890'),
('Rahul Sharma', 'rsharma@university.edu', '555-765-4321', 'Data Science', 2020, 3, '0000-0003-4567-8901'),
('Meera Joshi', 'mjoshi@university.edu', '555-654-3210', 'Artificial Intelligence', 2023, 4, '0000-0001-5678-9012'),
('Sanjay Malhotra', 'smalhotra@university.edu', '555-543-2109', 'Cybersecurity', 2022, 5, '0000-0002-6789-0123'),
('Nisha Reddy', 'nreddy@university.edu', '555-432-1098', 'Computer Science', 2021, 1, '0000-0003-7890-1234'),
('Ravi Krishnan', 'rkrishnan@university.edu', '555-321-0987', 'Data Science', 2023, 3, '0000-0001-8901-2345'),
('Ananya Das', 'adas@university.edu', '555-210-9876', 'Artificial Intelligence', 2022, 4, '0000-0002-9012-3456');

-- Sample Projects
INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
('Machine Learning for Climate Data Analysis', 1, 1, '2022-01-15', '2023-06-30', 'Completed'),
('Next-Generation Wireless Communication Systems', 2, 2, '2022-03-10', NULL, 'In Progress'),
('Predictive Analytics for Healthcare Outcomes', 3, 3, '2021-09-01', '2023-08-15', 'Completed'),
('Autonomous Vehicle Decision Systems', 4, 4, '2023-02-20', NULL, 'In Progress'),
('Blockchain Security Frameworks', 5, 5, '2022-11-05', NULL, 'In Progress'),
('Cloud-Based Natural Language Processing', 6, 1, '2022-05-12', '2023-07-01', 'Completed'),
('Quantum Computing Applications', 7, 3, '2023-01-10', NULL, 'In Progress'),
('Computer Vision for Medical Imaging', 8, 4, '2022-08-15', '2023-09-30', 'Completed'),
('Smart Grid Optimization Algorithms', 2, 2, '2022-12-01', NULL, 'In Progress'),
('Privacy-Preserving Data Mining', 5, 5, '2023-04-01', NULL, 'Not Started');

-- Sample Publications
INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
('Advancements in Neural Network Architectures for Climate Prediction', 1, 1, '2023-05-15', '10.1234/journal.2023.001'),
('Performance Analysis of 6G Network Prototypes', 2, 2, '2023-07-20', '10.1234/journal.2023.002'),
('Machine Learning Models for Early Disease Detection', 3, 3, '2022-11-30', '10.1234/journal.2022.003'),
('Ethical Frameworks for Autonomous Vehicle Decision Making', 4, 4, '2023-08-10', '10.1234/journal.2023.004'),
('Securing Blockchain Applications Against Quantum Threats', 5, 5, '2023-03-25', '10.1234/journal.2023.005'),
('Efficient Cloud-Based NLP for Resource-Constrained Devices', 6, 1, '2023-06-18', '10.1234/journal.2023.006'),
('Practical Applications of Quantum Algorithms in Data Science', 7, 3, '2023-09-05', '10.1234/journal.2023.007'),
('Deep Learning Approaches for Medical Image Segmentation', 8, 4, '2023-08-22', '10.1234/journal.2023.008'),
('Neural Networks for Climate Modeling: A Review', 1, 1, '2022-07-12', '10.1234/journal.2022.009'),
('Optimization Algorithms for Smart Energy Distribution', 2, 2, '2022-09-30', '10.1234/journal.2022.010'),
('Predictive Analytics in Preventative Healthcare', 3, 3, '2021-12-15', '10.1234/journal.2021.011'),
('Computer Vision Systems for Autonomous Navigation', 4, 4, '2022-06-28', '10.1234/journal.2022.012'),
('Cybersecurity Threats in Distributed Systems', 5, 5, '2022-10-10', '10.1234/journal.2022.013'),
('Natural Language Understanding in Clinical Settings', 6, 1, '2022-08-05', '10.1234/journal.2022.014'),
('Quantum Computing: Current State and Future Directions', 7, 3, '2022-04-18', '10.1234/journal.2022.015'); 