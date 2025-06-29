<?php
require_once 'db.php';
include 'header.php';

// Initialize search variable
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (empty($search)) {
    // Redirect to home if no search term
    $_SESSION['error'] = "Please enter a search term";
    header("Location: index.php");
    exit();
}

// Search in each entity
$results = array(
    'supervisors' => array(),
    'researchers' => array(),
    'projects' => array(),
    'publications' => array()
);

// Search supervisors
$sql = "SELECT * FROM Supervisor WHERE 
        Name LIKE ? OR 
        Department LIKE ? OR 
        Email LIKE ? OR 
        ORCID LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
$stmt->execute();
$results['supervisors'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Search researchers
$sql = "SELECT r.*, s.Name as SupervisorName 
        FROM Researcher r 
        LEFT JOIN Supervisor s ON r.Supervisor_ID = s.Supervisor_ID 
        WHERE r.Name LIKE ? OR 
        r.Email LIKE ? OR 
        r.Department LIKE ? OR 
        r.ORCID LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
$stmt->execute();
$results['researchers'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Search projects
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Project p 
        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
        WHERE p.Title LIKE ? OR 
        p.Status LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$results['projects'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Search publications
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Publication p 
        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
        WHERE p.Title LIKE ? OR 
        p.DOI LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$results['publications'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total results
$totalResults = count($results['supervisors']) + count($results['researchers']) + 
                count($results['projects']) + count($results['publications']);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
            <p>Found <?php echo $totalResults; ?> results</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search Results</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="global_search.php" class="search-container">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search across all entities..." 
                           value="<?php echo htmlspecialchars($search); ?>" required>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs for results categories -->
    <ul class="nav nav-tabs" id="searchTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                All Results (<?php echo $totalResults; ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="supervisors-tab" data-bs-toggle="tab" data-bs-target="#supervisors" type="button" role="tab" aria-controls="supervisors" aria-selected="false">
                Supervisors (<?php echo count($results['supervisors']); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="researchers-tab" data-bs-toggle="tab" data-bs-target="#researchers" type="button" role="tab" aria-controls="researchers" aria-selected="false">
                Researchers (<?php echo count($results['researchers']); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="false">
                Projects (<?php echo count($results['projects']); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="publications-tab" data-bs-toggle="tab" data-bs-target="#publications" type="button" role="tab" aria-controls="publications" aria-selected="false">
                Publications (<?php echo count($results['publications']); ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="searchTabsContent">
        <!-- All Results Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            <?php if ($totalResults === 0): ?>
                <div class="alert alert-info mt-3">No results found for "<?php echo htmlspecialchars($search); ?>"</div>
            <?php else: ?>
                <!-- Supervisors Section -->
                <?php if (count($results['supervisors']) > 0): ?>
                    <div class="mt-4">
                        <h3>Supervisors (<?php echo count($results['supervisors']); ?>)</h3>
                        <div class="list-group">
                            <?php foreach ($results['supervisors'] as $supervisor): ?>
                                <a href="supervisor/edit.php?id=<?php echo $supervisor['Supervisor_ID']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($supervisor['Name']); ?></h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($supervisor['Department']); ?></small>
                                    </div>
                                    <p class="mb-1">Email: <?php echo htmlspecialchars($supervisor['Email']); ?></p>
                                    <?php if (!empty($supervisor['ORCID'])): ?>
                                        <small class="text-muted">ORCID: <?php echo htmlspecialchars($supervisor['ORCID']); ?></small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Researchers Section -->
                <?php if (count($results['researchers']) > 0): ?>
                    <div class="mt-4">
                        <h3>Researchers (<?php echo count($results['researchers']); ?>)</h3>
                        <div class="list-group">
                            <?php foreach ($results['researchers'] as $researcher): ?>
                                <a href="researcher/edit.php?id=<?php echo $researcher['Researcher_ID']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($researcher['Name']); ?></h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($researcher['Department']); ?> (<?php echo $researcher['Enrollment_Year']; ?>)</small>
                                    </div>
                                    <p class="mb-1">
                                        Email: <?php echo htmlspecialchars($researcher['Email']); ?> | 
                                        Supervisor: <?php echo !empty($researcher['SupervisorName']) ? htmlspecialchars($researcher['SupervisorName']) : 'None'; ?>
                                    </p>
                                    <?php if (!empty($researcher['ORCID'])): ?>
                                        <small class="text-muted">ORCID: <?php echo htmlspecialchars($researcher['ORCID']); ?></small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Projects Section -->
                <?php if (count($results['projects']) > 0): ?>
                    <div class="mt-4">
                        <h3>Projects (<?php echo count($results['projects']); ?>)</h3>
                        <div class="list-group">
                            <?php foreach ($results['projects'] as $project): ?>
                                <a href="project/edit.php?id=<?php echo $project['Project_ID']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($project['Title']); ?></h5>
                                        <span class="badge <?php echo ($project['Status'] == 'Completed') ? 'bg-success' : (($project['Status'] == 'In Progress') ? 'bg-primary' : 'bg-warning'); ?>"><?php echo $project['Status']; ?></span>
                                    </div>
                                    <p class="mb-1">
                                        Researcher: <?php echo !empty($project['ResearcherName']) ? htmlspecialchars($project['ResearcherName']) : 'None'; ?> | 
                                        Supervisor: <?php echo !empty($project['SupervisorName']) ? htmlspecialchars($project['SupervisorName']) : 'None'; ?>
                                    </p>
                                    <small class="text-muted">
                                        Started: <?php echo date('Y-m-d', strtotime($project['Start_Date'])); ?> 
                                        <?php if ($project['End_Date']): ?>
                                            | Ended: <?php echo date('Y-m-d', strtotime($project['End_Date'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Publications Section -->
                <?php if (count($results['publications']) > 0): ?>
                    <div class="mt-4">
                        <h3>Publications (<?php echo count($results['publications']); ?>)</h3>
                        <div class="list-group">
                            <?php foreach ($results['publications'] as $publication): ?>
                                <a href="publication/edit.php?id=<?php echo $publication['Publication_ID']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($publication['Title']); ?></h5>
                                        <small class="text-muted"><?php echo date('Y-m-d', strtotime($publication['Date_Published'])); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        Researcher: <?php echo !empty($publication['ResearcherName']) ? htmlspecialchars($publication['ResearcherName']) : 'None'; ?> | 
                                        Supervisor: <?php echo !empty($publication['SupervisorName']) ? htmlspecialchars($publication['SupervisorName']) : 'None'; ?>
                                    </p>
                                    <?php if (!empty($publication['DOI'])): ?>
                                        <small class="text-muted">DOI: <?php echo htmlspecialchars($publication['DOI']); ?></small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Supervisors Tab -->
        <div class="tab-pane fade" id="supervisors" role="tabpanel" aria-labelledby="supervisors-tab">
            <?php if (count($results['supervisors']) === 0): ?>
                <div class="alert alert-info mt-3">No supervisors found for "<?php echo htmlspecialchars($search); ?>"</div>
            <?php else: ?>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>ORCID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['supervisors'] as $supervisor): ?>
                                <tr>
                                    <td><?php echo $supervisor['Supervisor_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($supervisor['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($supervisor['Department']); ?></td>
                                    <td><?php echo htmlspecialchars($supervisor['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($supervisor['Phone']); ?></td>
                                    <td><?php echo htmlspecialchars($supervisor['ORCID']); ?></td>
                                    <td>
                                        <a href="supervisor/edit.php?id=<?php echo $supervisor['Supervisor_ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="supervisor/delete.php?id=<?php echo $supervisor['Supervisor_ID']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Researchers Tab -->
        <div class="tab-pane fade" id="researchers" role="tabpanel" aria-labelledby="researchers-tab">
            <?php if (count($results['researchers']) === 0): ?>
                <div class="alert alert-info mt-3">No researchers found for "<?php echo htmlspecialchars($search); ?>"</div>
            <?php else: ?>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Supervisor</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['researchers'] as $researcher): ?>
                                <tr>
                                    <td><?php echo $researcher['Researcher_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($researcher['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($researcher['Department']); ?></td>
                                    <td><?php echo htmlspecialchars($researcher['Email']); ?></td>
                                    <td><?php echo !empty($researcher['SupervisorName']) ? htmlspecialchars($researcher['SupervisorName']) : 'None'; ?></td>
                                    <td><?php echo $researcher['Enrollment_Year']; ?></td>
                                    <td>
                                        <a href="researcher/edit.php?id=<?php echo $researcher['Researcher_ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="researcher/delete.php?id=<?php echo $researcher['Researcher_ID']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Projects Tab -->
        <div class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="projects-tab">
            <?php if (count($results['projects']) === 0): ?>
                <div class="alert alert-info mt-3">No projects found for "<?php echo htmlspecialchars($search); ?>"</div>
            <?php else: ?>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Researcher</th>
                                <th>Supervisor</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['projects'] as $project): ?>
                                <tr>
                                    <td><?php echo $project['Project_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($project['Title']); ?></td>
                                    <td><?php echo !empty($project['ResearcherName']) ? htmlspecialchars($project['ResearcherName']) : 'None'; ?></td>
                                    <td><?php echo !empty($project['SupervisorName']) ? htmlspecialchars($project['SupervisorName']) : 'None'; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($project['Start_Date'])); ?></td>
                                    <td><?php echo $project['End_Date'] ? date('Y-m-d', strtotime($project['End_Date'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($project['Status'] == 'Completed') ? 'bg-success' : (($project['Status'] == 'In Progress') ? 'bg-primary' : 'bg-warning'); ?>">
                                            <?php echo $project['Status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="project/edit.php?id=<?php echo $project['Project_ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="project/delete.php?id=<?php echo $project['Project_ID']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Publications Tab -->
        <div class="tab-pane fade" id="publications" role="tabpanel" aria-labelledby="publications-tab">
            <?php if (count($results['publications']) === 0): ?>
                <div class="alert alert-info mt-3">No publications found for "<?php echo htmlspecialchars($search); ?>"</div>
            <?php else: ?>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Researcher</th>
                                <th>Supervisor</th>
                                <th>Publication Date</th>
                                <th>DOI</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['publications'] as $publication): ?>
                                <tr>
                                    <td><?php echo $publication['Publication_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($publication['Title']); ?></td>
                                    <td><?php echo !empty($publication['ResearcherName']) ? htmlspecialchars($publication['ResearcherName']) : 'None'; ?></td>
                                    <td><?php echo !empty($publication['SupervisorName']) ? htmlspecialchars($publication['SupervisorName']) : 'None'; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($publication['Date_Published'])); ?></td>
                                    <td><?php echo !empty($publication['DOI']) ? htmlspecialchars($publication['DOI']) : 'N/A'; ?></td>
                                    <td>
                                        <a href="publication/edit.php?id=<?php echo $publication['Publication_ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="publication/delete.php?id=<?php echo $publication['Publication_ID']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 