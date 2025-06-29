<?php
require_once '../db.php';
include '../header.php';

// Initialize search variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['search_field']) ? $_GET['search_field'] : 'name';

// Prepare the query based on search
$sql = "SELECT * FROM Supervisor WHERE 1=1";
$params = array();

if (!empty($search)) {
    if ($searchField === 'name') {
        $sql .= " AND Name LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'department') {
        $sql .= " AND Department LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'id') {
        $sql .= " AND Supervisor_ID = :search";
        $params[':search'] = $search;
    }
}

$sql .= " ORDER BY Name ASC";

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
            <h2>Supervisors</h2>
            <a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Supervisor</a>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Supervisors</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" action="" class="search-container">
            <div class="input-group">
                <select name="search_field" class="form-select" style="max-width: 150px;">
                    <option value="name" <?php echo $searchField === 'name' ? 'selected' : ''; ?>>Name</option>
                    <option value="department" <?php echo $searchField === 'department' ? 'selected' : ''; ?>>Department</option>
                    <option value="id" <?php echo $searchField === 'id' ? 'selected' : ''; ?>>ID</option>
                </select>
                <input type="text" name="search" class="form-control" placeholder="Search supervisors..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="view.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
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
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>ORCID</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo $row['Supervisor_ID']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Department']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ORCID'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['Supervisor_ID']; ?>" class="btn btn-sm btn-primary btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $row['Supervisor_ID']; ?>" class="btn btn-sm btn-danger btn-action delete-btn">
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
                        No supervisors found<?php echo !empty($search) ? ' for your search criteria' : ''; ?>. 
                        <a href="add.php" class="alert-link">Add a new supervisor</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php'; 
?> 