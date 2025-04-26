<?php
require_once '../db.php';
include '../header.php';

// Initialize search variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['search_field']) ? $_GET['search_field'] : 'title';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Prepare the query based on search
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Project p 
        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
        WHERE 1=1";
$params = array();

if (!empty($search)) {
    if ($searchField === 'title') {
        $sql .= " AND p.Title LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'researcher') {
        $sql .= " AND r.Name LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'supervisor') {
        $sql .= " AND s.Name LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'id') {
        $sql .= " AND p.Project_ID = :search";
        $params[':search'] = $search;
    }
}

if (!empty($statusFilter)) {
    $sql .= " AND p.Status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY p.Start_Date DESC";

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Projects</h2>
            <a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Project</a>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Projects</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" action="" class="search-container">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <select name="search_field" class="form-select" style="max-width: 150px;">
                            <option value="title" <?php echo $searchField === 'title' ? 'selected' : ''; ?>>Title</option>
                            <option value="researcher" <?php echo $searchField === 'researcher' ? 'selected' : ''; ?>>Researcher</option>
                            <option value="supervisor" <?php echo $searchField === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                            <option value="id" <?php echo $searchField === 'id' ? 'selected' : ''; ?>>ID</option>
                        </select>
                        <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Not Started" <?php echo $statusFilter === 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                        <option value="In Progress" <?php echo $statusFilter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="On Hold" <?php echo $statusFilter === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                        <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="d-flex">
                        <button class="btn btn-primary me-2" type="submit">Search</button>
                        <?php if (!empty($search) || !empty($statusFilter)): ?>
                            <a href="view.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?php if (count($results) > 0): ?>
                    <div class="table-responsive">
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
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo $row['Project_ID']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                                        <td><?php echo $row['ResearcherName'] ? htmlspecialchars($row['ResearcherName']) : '<span class="text-muted">Not assigned</span>'; ?></td>
                                        <td><?php echo $row['SupervisorName'] ? htmlspecialchars($row['SupervisorName']) : '<span class="text-muted">Not assigned</span>'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['Start_Date'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($row['End_Date']) {
                                                echo date('M d, Y', strtotime($row['End_Date']));
                                            } else {
                                                echo '<span class="text-muted">Ongoing</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusClass = 'secondary';
                                            switch ($row['Status']) {
                                                case 'Not Started':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'In Progress':
                                                    $statusClass = 'info';
                                                    break;
                                                case 'Completed':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'On Hold':
                                                    $statusClass = 'secondary';
                                                    break;
                                                case 'Cancelled':
                                                    $statusClass = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $row['Status']; ?></span>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['Project_ID']; ?>" class="btn btn-sm btn-primary btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $row['Project_ID']; ?>" class="btn btn-sm btn-danger btn-action delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No projects found<?php echo !empty($search) || !empty($statusFilter) ? ' for your search criteria' : ''; ?>. 
                        <a href="add.php" class="alert-link">Add a new project</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php'; 
?> 