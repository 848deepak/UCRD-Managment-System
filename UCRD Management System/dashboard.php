<?php
require_once 'db.php';
include 'header.php';

// Count entities
$counts = array();

$sql = "SELECT COUNT(*) as count FROM Researcher";
$stmt = $conn->query($sql);
$counts['researchers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$sql = "SELECT COUNT(*) as count FROM Supervisor";
$stmt = $conn->query($sql);
$counts['supervisors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$sql = "SELECT COUNT(*) as count FROM Project";
$stmt = $conn->query($sql);
$counts['projects'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$sql = "SELECT COUNT(*) as count FROM Publication";
$stmt = $conn->query($sql);
$counts['publications'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get publication count by year
$sql = "SELECT strftime('%Y', Date_Published) as year, COUNT(*) as count 
        FROM Publication 
        GROUP BY strftime('%Y', Date_Published) 
        ORDER BY year";
$stmt = $conn->query($sql);
$publicationYears = array();
$publicationCounts = array();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $publicationYears[] = $row['year'];
    $publicationCounts[] = $row['count'];
}

// Get project status distribution
$sql = "SELECT Status, COUNT(*) as count 
        FROM Project 
        GROUP BY Status 
        ORDER BY count DESC";
$stmt = $conn->query($sql);
$projectStatus = array();
$projectStatusCounts = array();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $projectStatus[] = $row['Status'];
    $projectStatusCounts[] = $row['count'];
}

// Get top 5 researchers by publication count
$sql = "SELECT r.Name, COUNT(p.Publication_ID) as pub_count 
        FROM Researcher r 
        LEFT JOIN Publication p ON r.Researcher_ID = p.Researcher_ID 
        GROUP BY r.Researcher_ID 
        ORDER BY pub_count DESC 
        LIMIT 5";
$stmt = $conn->query($sql);
$topResearchers = array();
$topResearcherCounts = array();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $topResearchers[] = $row['Name'];
    $topResearcherCounts[] = $row['pub_count'];
}

// Get department distribution
$sql = "SELECT Department, COUNT(*) as count 
        FROM Researcher 
        GROUP BY Department 
        ORDER BY count DESC";
$stmt = $conn->query($sql);
$departments = array();
$departmentCounts = array();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $departments[] = $row['Department'];
    $departmentCounts[] = $row['count'];
}

// Get recent publications (last 5)
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Publication p 
        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
        ORDER BY p.Date_Published DESC 
        LIMIT 5";
$stmt = $conn->query($sql);
$recentPublications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recently updated projects (last 5)
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Project p 
        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
        ORDER BY p.Start_Date DESC 
        LIMIT 5";
$stmt = $conn->query($sql);
$recentProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>UCRD Dashboard</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Advanced Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Researchers</h5>
                            <h2 class="display-4"><?php echo $counts['researchers']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="researcher/view.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Supervisors</h5>
                            <h2 class="display-4"><?php echo $counts['supervisors']; ?></h2>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="supervisor/view.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Projects</h5>
                            <h2 class="display-4"><?php echo $counts['projects']; ?></h2>
                        </div>
                        <i class="fas fa-project-diagram fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="project/view.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Publications</h5>
                            <h2 class="display-4"><?php echo $counts['publications']; ?></h2>
                        </div>
                        <i class="fas fa-book fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="publication/view.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Publications by Year
                </div>
                <div class="card-body">
                    <canvas id="publicationsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Project Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="projectStatusChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- More Charts Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-trophy me-1"></i>
                    Top Researchers by Publications
                </div>
                <div class="card-body">
                    <canvas id="topResearchersChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-building me-1"></i>
                    Researchers by Department
                </div>
                <div class="card-body">
                    <canvas id="departmentsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i>
                    Recent Publications
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($recentPublications) > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Researcher</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPublications as $pub): ?>
                                        <tr>
                                            <td>
                                                <a href="publication/edit.php?id=<?php echo $pub['Publication_ID']; ?>">
                                                    <?php echo htmlspecialchars($pub['Title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($pub['ResearcherName']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($pub['Date_Published'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No recent publications.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    <a href="publication/view.php" class="btn btn-sm btn-outline-primary">View All Publications</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tasks me-1"></i>
                    Recent Projects
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php if (count($recentProjects) > 0): ?>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Researcher</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProjects as $proj): ?>
                                        <tr>
                                            <td>
                                                <a href="project/edit.php?id=<?php echo $proj['Project_ID']; ?>">
                                                    <?php echo htmlspecialchars($proj['Title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($proj['ResearcherName']); ?></td>
                                            <td>
                                                <span class="badge <?php echo ($proj['Status'] == 'Completed') ? 'bg-success' : (($proj['Status'] == 'In Progress') ? 'bg-primary' : 'bg-warning'); ?>">
                                                    <?php echo $proj['Status']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No recent projects.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    <a href="project/view.php" class="btn btn-sm btn-outline-primary">View All Projects</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-link me-1"></i>
                    Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="researcher/add.php" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-user-plus me-2"></i> Add Researcher
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="supervisor/add.php" class="btn btn-success btn-block w-100">
                                <i class="fas fa-user-tie me-2"></i> Add Supervisor
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="project/add.php" class="btn btn-info btn-block w-100">
                                <i class="fas fa-folder-plus me-2"></i> Add Project
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="publication/add.php" class="btn btn-danger btn-block w-100">
                                <i class="fas fa-file-medical me-2"></i> Add Publication
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="global_search.php?search=2023" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-search me-2"></i> Search 2023 Records
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="relationships.php" class="btn btn-dark btn-block w-100">
                                <i class="fas fa-project-diagram me-2"></i> View Relationship Network
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="publication/view.php?export=pdf" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-file-pdf me-2"></i> Export Publications as PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Publications by Year Chart
    var ctx = document.getElementById('publicationsChart').getContext('2d');
    var pubChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($publicationYears); ?>,
            datasets: [{
                label: 'Number of Publications',
                data: <?php echo json_encode($publicationCounts); ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Project Status Chart
    var projectCtx = document.getElementById('projectStatusChart').getContext('2d');
    var projectChart = new Chart(projectCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($projectStatus); ?>,
            datasets: [{
                data: <?php echo json_encode($projectStatusCounts); ?>,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(0, 123, 255, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(108, 117, 125, 0.7)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(0, 123, 255, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    
    // Top Researchers Chart
    var researcherCtx = document.getElementById('topResearchersChart').getContext('2d');
    var researcherChart = new Chart(researcherCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($topResearchers); ?>,
            datasets: [{
                label: 'Publication Count',
                data: <?php echo json_encode($topResearcherCounts); ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Departments Chart
    var deptCtx = document.getElementById('departmentsChart').getContext('2d');
    var deptChart = new Chart(deptCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($departments); ?>,
            datasets: [{
                data: <?php echo json_encode($departmentCounts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?> 