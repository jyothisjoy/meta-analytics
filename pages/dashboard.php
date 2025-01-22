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
                            <label>Start Date:</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3">
                            <label>End Date:</label>
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
                            <button class="btn btn-primary form-control" onclick="updateDashboard()">
                                Update Dashboard
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
    // Set default dates
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
    
    // Initialize charts with type: 'line'
    const chartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            hover: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: {
                        stepSize: 50
                    }
                }
            }
        }
    };

    trafficChart = new Chart(
        document.getElementById('trafficChart').getContext('2d'),
        {...chartConfig}
    );
    
    bookingsChart = new Chart(
        document.getElementById('bookingsChart').getContext('2d'),
        {...chartConfig}
    );

    // Load initial data
    updateDashboard();
}

function updateDashboard() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    fetch(`../api/dashboard.php?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCharts(data);
            } else {
                alert('Error loading dashboard data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load dashboard data');
        });
}

function updateCharts(data) {
    // Process traffic data
    const trafficData = processTrafficData(data.traffic);
    trafficChart.data = {
        labels: trafficData.hotels,  // Y-axis: hotels
        datasets: trafficData.dates.map((date, index) => ({
            label: date,
            data: trafficData.values.map(hotelData => hotelData[index]),
            borderColor: getColorForIndex(index),
            backgroundColor: getColorForIndex(index),
            fill: false,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5
        }))
    };
    trafficChart.options = {
        responsive: true,
        indexAxis: 'y',  // Makes it horizontal
        plugins: {
            legend: {
                position: 'top',
                align: 'start'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Visitors'
                },
                grid: {
                    display: true
                }
            },
            y: {
                grid: {
                    display: false
                }
            }
        }
    };
    trafficChart.update();

    // Process booking data
    const bookingData = processBookingData(data.bookings);
    bookingsChart.data = {
        labels: bookingData.hotels,  // Y-axis: hotels
        datasets: bookingData.dates.map((date, index) => ({
            label: date,
            data: bookingData.values.map(hotelData => hotelData[index]),
            borderColor: getColorForIndex(index),
            backgroundColor: getColorForIndex(index),
            fill: false,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5
        }))
    };
    bookingsChart.options = {
        responsive: true,
        indexAxis: 'y',  // Makes it horizontal
        plugins: {
            legend: {
                position: 'top',
                align: 'start'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Bookings'
                },
                grid: {
                    display: true
                }
            },
            y: {
                grid: {
                    display: false
                }
            }
        }
    };
    bookingsChart.update();
}

function getColorForIndex(index, alpha = 1) {
    // Color palette for the lines
    const colors = [
        `rgba(255, 99, 132, ${alpha})`,   // red
        `rgba(54, 162, 235, ${alpha})`,   // blue
        `rgba(255, 206, 86, ${alpha})`,   // yellow
        `rgba(75, 192, 192, ${alpha})`,   // green
        `rgba(153, 102, 255, ${alpha})`,  // purple
        `rgba(255, 159, 64, ${alpha})`,   // orange
        `rgba(199, 199, 199, ${alpha})`   // gray
    ];
    return colors[index % colors.length];
}

function processTrafficData(data) {
    const hotels = [...new Set(data.map(item => item.hotel_name))];
    const dates = [...new Set(data.map(item => item.date))];
    
    // Create a matrix of values for each hotel and date
    const values = hotels.map(hotel => 
        dates.map(date => {
            const entry = data.find(item => 
                item.hotel_name === hotel && 
                item.date === date
            );
            return entry ? entry.new_users : 0;
        })
    );

    return {
        hotels: hotels,
        dates: dates,
        values: values
    };
}

function processBookingData(data) {
    const hotels = [...new Set(data.map(item => item.hotel_name))];
    const dates = [...new Set(data.map(item => item.date))];
    
    // Create a matrix of values for each hotel and date
    const values = hotels.map(hotel => 
        dates.map(date => {
            const entry = data.find(item => 
                item.hotel_name === hotel && 
                item.date === date
            );
            return entry ? entry.actual_bookings : 0;
        })
    );

    return {
        hotels: hotels,
        dates: dates,
        values: values
    };
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', initializeCharts);
</script>

<?php require_once '../includes/footer.php'; ?> 