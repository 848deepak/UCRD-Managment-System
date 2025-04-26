<?php
require_once '../db.php';

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    // Include TCPDF library
    require_once('../tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('UCRD Management System');
    $pdf->SetAuthor('UCRD Management System');
    $pdf->SetTitle('Publications List');
    $pdf->SetSubject('Publications List');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'UCRD Management System', 'Publications List - Generated on ' . date('Y-m-d H:i:s'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont('courier');
    
    // Set margins
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Get publications data
    $sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
            FROM Publication p 
            LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
            LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
            ORDER BY p.Date_Published DESC";
    $stmt = $conn->query($sql);
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create table header
    $html = '<table border="1" cellpadding="5">
                <thead>
                    <tr bgcolor="#CCCCCC">
                        <th width="8%">ID</th>
                        <th width="35%">Title</th>
                        <th width="15%">Researcher</th>
                        <th width="15%">Supervisor</th>
                        <th width="12%">Date</th>
                        <th width="15%">DOI</th>
                    </tr>
                </thead>
                <tbody>';
    
    // Add data to table
    foreach ($publications as $row) {
        $html .= '<tr>
                    <td>' . $row['Publication_ID'] . '</td>
                    <td>' . htmlspecialchars($row['Title']) . '</td>
                    <td>' . ($row['ResearcherName'] ? htmlspecialchars($row['ResearcherName']) : 'Not assigned') . '</td>
                    <td>' . ($row['SupervisorName'] ? htmlspecialchars($row['SupervisorName']) : 'Not assigned') . '</td>
                    <td>' . date('Y-m-d', strtotime($row['Date_Published'])) . '</td>
                    <td>' . (!empty($row['DOI']) ? htmlspecialchars($row['DOI']) : 'N/A') . '</td>
                  </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Output HTML table
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('publications_list.pdf', 'D');
    exit();
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="publications.csv"');
    
    // Create file pointer for output
    $output = fopen('php://output', 'w');
    
    // Output CSV header row
    fputcsv($output, array('ID', 'Title', 'Researcher', 'Supervisor', 'Publication Date', 'DOI'), ',', '"', '\\');
    
    // Get publications data
    $sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
            FROM Publication p 
            LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
            LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
            ORDER BY p.Date_Published DESC";
    $stmt = $conn->query($sql);
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output each row of data
    foreach ($publications as $row) {
        fputcsv($output, array(
            $row['Publication_ID'],
            $row['Title'],
            $row['ResearcherName'] ? $row['ResearcherName'] : 'Not assigned',
            $row['SupervisorName'] ? $row['SupervisorName'] : 'Not assigned',
            $row['Date_Published'],
            !empty($row['DOI']) ? $row['DOI'] : 'N/A'
        ), ',', '"', '\\');
    }
    
    // Close the file pointer
    fclose($output);
    exit();
}

// Now include the header which outputs HTML
include '../header.php';

// Initialize search variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['search_field']) ? $_GET['search_field'] : 'title';
$searchYear = isset($_GET['year']) ? $_GET['year'] : '';

// Get distinct years for the filter dropdown
$years = array();
$sql = "SELECT DISTINCT strftime('%Y', Date_Published) as year FROM Publication ORDER BY year DESC";
$stmt = $conn->query($sql);
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Prepare the query based on search
$sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
        FROM Publication p 
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
    } elseif ($searchField === 'doi') {
        $sql .= " AND p.DOI LIKE :search";
        $params[':search'] = "%$search%";
    } elseif ($searchField === 'id') {
        $sql .= " AND p.Publication_ID = :search";
        $params[':search'] = $search;
    }
}

if (!empty($searchYear)) {
    $sql .= " AND strftime('%Y', p.Date_Published) = :year";
    $params[':year'] = $searchYear;
}

$sql .= " ORDER BY p.Date_Published DESC";

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
            <h2>Publications</h2>
            <div>
                <a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Publication</a>
                <div class="btn-group ms-2">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?export=csv<?php echo !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $searchField : ''; ?><?php echo !empty($searchYear) ? '&year=' . $searchYear : ''; ?>">Export to CSV</a></li>
                        <li><a class="dropdown-item" href="?export=pdf<?php echo !empty($search) ? '&search=' . urlencode($search) . '&search_field=' . $searchField : ''; ?><?php echo !empty($searchYear) ? '&year=' . $searchYear : ''; ?>">Export to PDF</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Publications</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" action="" class="search-container">
            <div class="input-group">
                <select name="search_field" class="form-select" style="max-width: 180px;">
                    <option value="title" <?php echo $searchField === 'title' ? 'selected' : ''; ?>>Title</option>
                    <option value="researcher" <?php echo $searchField === 'researcher' ? 'selected' : ''; ?>>Researcher</option>
                    <option value="supervisor" <?php echo $searchField === 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                    <option value="doi" <?php echo $searchField === 'doi' ? 'selected' : ''; ?>>DOI</option>
                    <option value="id" <?php echo $searchField === 'id' ? 'selected' : ''; ?>>ID</option>
                </select>
                <input type="text" name="search" class="form-control" placeholder="Search publications..." value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="year" class="form-select" style="max-width: 150px;">
                    <option value="">All Years</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $searchYear == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button class="btn btn-primary" type="submit">Search</button>
                <?php if (!empty($search) || !empty($searchYear)): ?>
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
                                    <th>Title</th>
                                    <th>Researcher</th>
                                    <th>Supervisor</th>
                                    <th>Publication Date</th>
                                    <th>DOI</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo $row['Publication_ID']; ?></td>
                                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                                        <td><?php echo $row['ResearcherName'] ? htmlspecialchars($row['ResearcherName']) : '<span class="text-muted">Not assigned</span>'; ?></td>
                                        <td><?php echo $row['SupervisorName'] ? htmlspecialchars($row['SupervisorName']) : '<span class="text-muted">Not assigned</span>'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['Date_Published'])); ?></td>
                                        <td>
                                            <?php if (!empty($row['DOI'])): ?>
                                                <a href="https://doi.org/<?php echo htmlspecialchars($row['DOI']); ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($row['DOI']); ?>
                                                    <i class="fas fa-external-link-alt ms-1 small"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['Publication_ID']; ?>" class="btn btn-sm btn-primary btn-action">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $row['Publication_ID']; ?>" class="btn btn-sm btn-danger btn-action delete-btn">
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
                        No publications found<?php echo !empty($search) || !empty($searchYear) ? ' for your search criteria' : ''; ?>. 
                        <a href="add.php" class="alert-link">Add a new publication</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include '../footer.php'; 
?> 