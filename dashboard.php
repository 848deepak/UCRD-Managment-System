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
$sql = "SELECT strftime('%Y', Date_Published) as year, COUNT(*) as count FROM Publication GROUP BY strftime('%Y', Date_Published) ORDER BY year";
$stmt = $conn->query($sql);
$publicationYears = array();
$publicationCounts = array();

if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $publicationYears[] = $row['year'];
        $publicationCounts[] = $row['count'];
    }
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

<div class="row mb-4">
    <div class="col-md-12">
        <div class="jumbotron bg-light p-5 rounded">
            <h1 class="display-4">Welcome to UCRD Management System</h1>
            <p class="lead">A comprehensive system for managing university researchers, supervisors, projects, and publications.</p>
            <hr class="my-4">
            <p>Use the dashboard cards below to navigate through the system or explore our enhanced features.</p>
            <div class="mt-3">
                <a href="dashboard.php" class="btn btn-primary me-2"><i class="fas fa-chart-line me-1"></i> Advanced Dashboard</a>
                <a href="relationships.php" class="btn btn-info me-2"><i class="fas fa-project-diagram me-1"></i> Researcher Network</a>
                <a href="global_search.php?search=2023" class="btn btn-secondary"><i class="fas fa-search me-1"></i> Search</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Researchers Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-dashboard text-center">
            <div class="card-body">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Researchers</h5>
                <p class="entity-count"><?php echo $counts['researchers']; ?></p>
                <div class="d-grid gap-2">
                    <a href="researcher/view.php" class="btn btn-primary">View All</a>
                    <a href="researcher/add.php" class="btn btn-outline-primary">Add New</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Supervisors Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-dashboard text-center">
            <div class="card-body">
                <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                <h5 class="card-title">Supervisors</h5>
                <p class="entity-count"><?php echo $counts['supervisors']; ?></p>
                <div class="d-grid gap-2">
                    <a href="supervisor/view.php" class="btn btn-success">View All</a>
                    <a href="supervisor/add.php" class="btn btn-outline-success">Add New</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Projects Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-dashboard text-center">
            <div class="card-body">
                <i class="fas fa-project-diagram fa-3x text-info mb-3"></i>
                <h5 class="card-title">Projects</h5>
                <p class="entity-count"><?php echo $counts['projects']; ?></p>
                <div class="d-grid gap-2">
                    <a href="project/view.php" class="btn btn-info">View All</a>
                    <a href="project/add.php" class="btn btn-outline-info">Add New</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Publications Card -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-dashboard text-center">
            <div class="card-body">
                <i class="fas fa-book fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Publications</h5>
                <p class="entity-count"><?php echo $counts['publications']; ?></p>
                <div class="d-grid gap-2">
                    <a href="publication/view.php" class="btn btn-danger">View All</a>
                    <a href="publication/add.php" class="btn btn-outline-danger">Add New</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5>Publications by Year</h5>
            </div>
            <div class="card-body">
                <canvas id="publicationsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5>System Overview</h5>
            </div>
            <div class="card-body">
                <p>This dashboard provides an overview of all entities in the UCRD Management System.</p>
                <p>Quick Stats:</p>
                <ul>
                    <li><strong>Researchers:</strong> <?php echo $counts['researchers']; ?></li>
                    <li><strong>Supervisors:</strong> <?php echo $counts['supervisors']; ?></li>
                    <li><strong>Projects:</strong> <?php echo $counts['projects']; ?></li>
                    <li><strong>Publications:</strong> <?php echo $counts['publications']; ?></li>
                </ul>
                
                <h6 class="mt-4">New Features:</h6>
                <ul>
                    <li><strong>Advanced Dashboard:</strong> Visualize research productivity and trends</li>
                    <li><strong>Global Search:</strong> Search across all entities</li>
                    <li><strong>Network Visualization:</strong> Explore researcher-supervisor relationships</li>
                    <li><strong>PDF/CSV Export:</strong> Generate reports for publications and other entities</li>
                </ul>
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
    var chart = new Chart(ctx, {
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
});
</script>

<?php include 'footer.php'; ?> 