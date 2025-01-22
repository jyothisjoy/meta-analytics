<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Date Range:</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3">
                            <label>To:</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3">
                            <label>Metric:</label>
                            <select class="form-control" id="metricSelect">
                                <option value="traffic">Traffic</option>
                                <option value="bookings">Bookings</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control" onclick="updateCharts()">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Traffic Overview</h5>
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bookings Overview</h5>
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let trafficChart = null;
let bookingsChart = null;

function initializeCharts() {
    // Traffic Chart
    const trafficCtx = document.getElementById('trafficChart').getContext('2d');
    trafficChart = new Chart(trafficCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
    bookingsChart = new Chart(bookingsCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateCharts() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const metric = document.getElementById('metricSelect').value;

    // Fetch data from server
    fetch(`../api/get_chart_data.php?start=${startDate}&end=${endDate}&metric=${metric}`)
        .then(response => response.json())
        .then(data => {
            updateChartsWithData(data);
        })
        .catch(error => console.error('Error:', error));
}

function updateChartsWithData(data) {
    // Update charts with the received data
    // This will be implemented when we create the API endpoint
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', initializeCharts);
</script>

<?php require_once '../includes/footer.php'; ?> 