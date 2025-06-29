<?php
require_once 'db.php';
include 'header.php';

// Get all supervisors
$sql = "SELECT * FROM Supervisor ORDER BY Name";
$stmt = $conn->query($sql);
$supervisors = [];
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $supervisors[$row['Supervisor_ID']] = $row;
}

// Get researchers with supervisor info
$sql = "SELECT r.*, s.Name as SupervisorName, s.Department as SupervisorDepartment 
        FROM Researcher r 
        LEFT JOIN Supervisor s ON r.Supervisor_ID = s.Supervisor_ID 
        ORDER BY r.Name";
$stmt = $conn->query($sql);
$researchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get publications count per researcher
$sql = "SELECT Researcher_ID, COUNT(*) as PublicationCount 
        FROM Publication 
        GROUP BY Researcher_ID";
$stmt = $conn->query($sql);
$publicationCounts = [];
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $publicationCounts[$row['Researcher_ID']] = $row['PublicationCount'];
}

// Get projects count per researcher
$sql = "SELECT Researcher_ID, COUNT(*) as ProjectCount 
        FROM Project 
        GROUP BY Researcher_ID";
$stmt = $conn->query($sql);
$projectCounts = [];
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $projectCounts[$row['Researcher_ID']] = $row['ProjectCount'];
}

// Prepare data for network visualization
$nodes = [];
$links = [];

// Add supervisor nodes
foreach ($supervisors as $supervisor) {
    $nodes[] = [
        'id' => 's' . $supervisor['Supervisor_ID'],
        'name' => $supervisor['Name'],
        'department' => $supervisor['Department'],
        'group' => 1, // Supervisors are group 1
        'size' => 20
    ];
}

// Add researcher nodes and links to supervisors
foreach ($researchers as $researcher) {
    // Calculate node size based on publications and projects (min size 10, max size 25)
    $pubCount = isset($publicationCounts[$researcher['Researcher_ID']]) ? $publicationCounts[$researcher['Researcher_ID']] : 0;
    $projCount = isset($projectCounts[$researcher['Researcher_ID']]) ? $projectCounts[$researcher['Researcher_ID']] : 0;
    $size = 10 + min(15, ($pubCount + $projCount) * 2);
    
    $nodes[] = [
        'id' => 'r' . $researcher['Researcher_ID'],
        'name' => $researcher['Name'],
        'department' => $researcher['Department'],
        'group' => 2, // Researchers are group 2
        'size' => $size,
        'publications' => $pubCount,
        'projects' => $projCount,
        'year' => $researcher['Enrollment_Year']
    ];
    
    // Create link if supervisor exists
    if ($researcher['Supervisor_ID']) {
        $links[] = [
            'source' => 'r' . $researcher['Researcher_ID'],
            'target' => 's' . $researcher['Supervisor_ID'],
            'value' => 1
        ];
    }
}

// Convert data to JSON for JavaScript
$jsonNodes = json_encode($nodes);
$jsonLinks = json_encode($links);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Researcher-Supervisor Network</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Relationship Network</li>
                </ol>
            </nav>
            <p class="lead">Visualizing relationships between researchers and supervisors.</p>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div id="network-visualization" style="width: 100%; height: 600px; border: 1px solid #ddd;"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Information</h5>
                                </div>
                                <div class="card-body">
                                    <div id="node-info">
                                        <p class="text-muted">Click on a node to see details.</p>
                                    </div>
                                    <hr>
                                    <div class="legend">
                                        <h6>Legend</h6>
                                        <div><span class="dot supervisor-dot"></span> Supervisors</div>
                                        <div><span class="dot researcher-dot"></span> Researchers</div>
                                        <p class="mt-2"><small>Node size indicates research output (publications + projects)</small></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>Filter</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Department</label>
                                        <select id="department-filter" class="form-select">
                                            <option value="">All Departments</option>
                                            <?php
                                            $departments = [];
                                            foreach ($researchers as $r) {
                                                if (!in_array($r['Department'], $departments)) {
                                                    $departments[] = $r['Department'];
                                                    echo '<option value="' . htmlspecialchars($r['Department']) . '">' . htmlspecialchars($r['Department']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div>
                                        <button id="reset-filter" class="btn btn-secondary btn-sm">Reset Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dot {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-right: 5px;
}
.supervisor-dot {
    background-color: #ff7f0e;
}
.researcher-dot {
    background-color: #1f77b4;
}
</style>

<!-- D3.js for network visualization -->
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the network visualization container
    const container = document.getElementById('network-visualization');
    const width = container.clientWidth;
    const height = container.clientHeight;
    
    // Parse the JSON data
    const nodes = <?php echo $jsonNodes; ?>;
    const links = <?php echo $jsonLinks; ?>;
    
    let filteredNodes = [...nodes];
    let filteredLinks = [...links];
    
    // Create the SVG container
    const svg = d3.select('#network-visualization')
        .append('svg')
        .attr('width', width)
        .attr('height', height);
    
    // Create a group for the network
    const g = svg.append('g');
    
    // Create a zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });
    
    // Apply zoom behavior to SVG
    svg.call(zoom);
    
    // Initialize the simulation
    const simulation = d3.forceSimulation()
        .force('link', d3.forceLink().id(d => d.id).distance(100))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(d => d.size + 5));
    
    // Create the links
    const link = g.append('g')
        .attr('class', 'links')
        .selectAll('line')
        .data(links)
        .enter()
        .append('line')
        .attr('stroke', '#999')
        .attr('stroke-opacity', 0.6)
        .attr('stroke-width', d => Math.sqrt(d.value));
    
    // Create the nodes
    const node = g.append('g')
        .attr('class', 'nodes')
        .selectAll('circle')
        .data(nodes)
        .enter()
        .append('circle')
        .attr('r', d => d.size)
        .attr('fill', d => d.group === 1 ? '#ff7f0e' : '#1f77b4')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended));
    
    // Add node labels
    const label = g.append('g')
        .attr('class', 'labels')
        .selectAll('text')
        .data(nodes)
        .enter()
        .append('text')
        .text(d => d.name)
        .attr('font-size', 10)
        .attr('dx', 12)
        .attr('dy', 4);
    
    // Add title (tooltip)
    node.append('title')
        .text(d => d.name);
    
    // Node click event to show info
    node.on('click', function(event, d) {
        const nodeInfo = document.getElementById('node-info');
        
        // Different info for supervisors and researchers
        if (d.group === 1) { // Supervisor
            nodeInfo.innerHTML = `
                <h6>${d.name}</h6>
                <p><strong>Type:</strong> Supervisor</p>
                <p><strong>Department:</strong> ${d.department}</p>
                <p><a href="supervisor/edit.php?id=${d.id.substring(1)}" class="btn btn-sm btn-primary">View Details</a></p>
            `;
        } else { // Researcher
            nodeInfo.innerHTML = `
                <h6>${d.name}</h6>
                <p><strong>Type:</strong> Researcher</p>
                <p><strong>Department:</strong> ${d.department}</p>
                <p><strong>Enrollment Year:</strong> ${d.year}</p>
                <p><strong>Publications:</strong> ${d.publications}</p>
                <p><strong>Projects:</strong> ${d.projects}</p>
                <p><a href="researcher/edit.php?id=${d.id.substring(1)}" class="btn btn-sm btn-primary">View Details</a></p>
            `;
        }
    });
    
    // Update the simulation on each tick
    simulation
        .nodes(nodes)
        .on('tick', ticked);
    
    simulation.force('link')
        .links(links);
    
    // Function to handle tick event
    function ticked() {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);
        
        node
            .attr('cx', d => d.x = Math.max(d.size, Math.min(width - d.size, d.x)))
            .attr('cy', d => d.y = Math.max(d.size, Math.min(height - d.size, d.y)));
        
        label
            .attr('x', d => d.x)
            .attr('y', d => d.y);
    }
    
    // Functions for drag behavior
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }
    
    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }
    
    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }
    
    // Filter by department
    const departmentFilter = document.getElementById('department-filter');
    departmentFilter.addEventListener('change', function() {
        const department = this.value;
        if (!department) {
            // Reset to original data
            updateVisualization(nodes, links);
        } else {
            // Filter nodes by department
            const filtered = nodes.filter(n => n.department === department || n.group === 1);
            
            // Get IDs of filtered nodes
            const filteredIds = filtered.map(n => n.id);
            
            // Filter links that connect to filtered nodes
            const filteredLinks = links.filter(l => 
                filteredIds.includes(l.source.id || l.source) && 
                filteredIds.includes(l.target.id || l.target)
            );
            
            updateVisualization(filtered, filteredLinks);
        }
    });
    
    // Reset filter button
    document.getElementById('reset-filter').addEventListener('click', function() {
        departmentFilter.value = '';
        updateVisualization(nodes, links);
    });
    
    // Update visualization with new data
    function updateVisualization(newNodes, newLinks) {
        // Update the simulation
        simulation.nodes(newNodes);
        simulation.force('link').links(newLinks);
        simulation.alpha(1).restart();
        
        // Update the links
        const linkUpdate = g.selectAll('.links line')
            .data(newLinks);
        
        linkUpdate.exit().remove();
        
        const linkEnter = linkUpdate.enter()
            .append('line')
            .attr('class', 'links')
            .attr('stroke', '#999')
            .attr('stroke-opacity', 0.6)
            .attr('stroke-width', d => Math.sqrt(d.value));
        
        // Update the nodes
        const nodeUpdate = g.selectAll('.nodes circle')
            .data(newNodes);
        
        nodeUpdate.exit().remove();
        
        const nodeEnter = nodeUpdate.enter()
            .append('circle')
            .attr('class', 'nodes')
            .attr('r', d => d.size)
            .attr('fill', d => d.group === 1 ? '#ff7f0e' : '#1f77b4')
            .call(d3.drag()
                .on('start', dragstarted)
                .on('drag', dragged)
                .on('end', dragended))
            .on('click', function(event, d) {
                const nodeInfo = document.getElementById('node-info');
                
                if (d.group === 1) {
                    nodeInfo.innerHTML = `
                        <h6>${d.name}</h6>
                        <p><strong>Type:</strong> Supervisor</p>
                        <p><strong>Department:</strong> ${d.department}</p>
                        <p><a href="supervisor/edit.php?id=${d.id.substring(1)}" class="btn btn-sm btn-primary">View Details</a></p>
                    `;
                } else {
                    nodeInfo.innerHTML = `
                        <h6>${d.name}</h6>
                        <p><strong>Type:</strong> Researcher</p>
                        <p><strong>Department:</strong> ${d.department}</p>
                        <p><strong>Enrollment Year:</strong> ${d.year}</p>
                        <p><strong>Publications:</strong> ${d.publications}</p>
                        <p><strong>Projects:</strong> ${d.projects}</p>
                        <p><a href="researcher/edit.php?id=${d.id.substring(1)}" class="btn btn-sm btn-primary">View Details</a></p>
                    `;
                }
            });
        
        nodeEnter.append('title')
            .text(d => d.name);
        
        // Update the labels
        const labelUpdate = g.selectAll('.labels text')
            .data(newNodes);
        
        labelUpdate.exit().remove();
        
        const labelEnter = labelUpdate.enter()
            .append('text')
            .attr('class', 'labels')
            .text(d => d.name)
            .attr('font-size', 10)
            .attr('dx', 12)
            .attr('dy', 4);
    }
});
</script>

<?php include 'footer.php'; ?> 