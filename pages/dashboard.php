<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <!-- Traffic Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Traffic Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date:</label>
                            <input type="date" class="form-control" id="trafficStartDate">
                        </div>
                        <div class="col-md-3">
                            <label>End Date:</label>
                            <input type="date" class="form-control" id="trafficEndDate">
                        </div>
                        <div class="col-md-3">
                            <label>Metric:</label>
                            <select class="form-control" id="trafficMetricSelect">
                                <option value="expected_traffic">Expected Traffic</option>
                                <option value="new_users">New Users</option>
                                <option value="bookings">Number of Bookings</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control" onclick="updateTrafficChart()">
                                Update Traffic
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bookings Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date:</label>
                            <input type="date" class="form-control" id="bookingsStartDate">
                        </div>
                        <div class="col-md-3">
                            <label>End Date:</label>
                            <input type="date" class="form-control" id="bookingsEndDate">
                        </div>
                        <div class="col-md-3">
                            <label>Metric:</label>
                            <select class="form-control" id="bookingsMetricSelect">
                                <option value="number_of_rooms">Number of Rooms</option>
                                <option value="booking_target">Booking Target</option>
                                <option value="actual_bookings">Actual Bookings</option>
                                <option value="booked_nights">Booked Nights</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control" onclick="updateBookingsChart()">
                                Update Bookings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
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
    
    // Set default dates for both filters
    ['traffic', 'bookings'].forEach(type => {
        document.getElementById(`${type}StartDate`).value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById(`${type}EndDate`).value = today.toISOString().split('T')[0];
    });
    
    // Initialize charts
    const chartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start'
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

    // Initial load
    updateTrafficChart();
    updateBookingsChart();
}

function updateTrafficChart() {
    const startDate = document.getElementById('trafficStartDate').value;
    const endDate = document.getElementById('trafficEndDate').value;
    const metric = document.getElementById('trafficMetricSelect').value;
    
    fetchAndUpdateChart('traffic', startDate, endDate, metric);
}

function updateBookingsChart() {
    const startDate = document.getElementById('bookingsStartDate').value;
    const endDate = document.getElementById('bookingsEndDate').value;
    const metric = document.getElementById('bookingsMetricSelect').value;
    
    fetchAndUpdateChart('bookings', startDate, endDate, metric);
}

function fetchAndUpdateChart(type, startDate, endDate, metric) {
    fetch(`../api/dashboard.php?type=${type}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChartData(type, data, metric);
            } else {
                alert(`Error loading ${type} data: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Failed to load ${type} data`);
        });
}

function getMetricTitle(metric) {
    const titles = {
        'expected_traffic': 'Expected Traffic',
        'new_users': 'New Users',
        'bookings': 'Number of Bookings',
        'number_of_rooms': 'Number of Rooms',
        'booking_target': 'Booking Target',
        'actual_bookings': 'Actual Bookings',
        'booked_nights': 'Booked Nights'
    };
    return titles[metric] || metric;
}

function getMetricValue(data, metric) {
    switch(metric) {
        case 'expected_traffic':
            return data.expected_traffic;
        case 'new_users':
            return data.new_users;
        case 'bookings':
            return data.bookings;
        case 'number_of_rooms':
            return data.number_of_rooms;
        case 'booking_target':
            return data.booking_target;
        case 'actual_bookings':
            return data.actual_bookings;
        case 'booked_nights':
            return data.booked_nights;
        default:
            return 0;
    }
}

function updateChartData(type, data, metric) {
    const selectedMetric = document.getElementById(`${type}MetricSelect`).value;
    const metricTitle = getMetricTitle(selectedMetric);
    
    // Determine if we're showing traffic or booking data
    const isTrafficMetric = ['expected_traffic', 'new_users', 'bookings'].includes(selectedMetric);
    const chartData = isTrafficMetric ? processTrafficData(data.traffic, selectedMetric) : processBookingData(data.bookings, selectedMetric);
    
    const chartToUpdate = isTrafficMetric ? trafficChart : bookingsChart;
    
    chartToUpdate.data = {
        labels: chartData.hotels,
        datasets: chartData.dates.map((date, index) => ({
            label: date,
            data: chartData.values.map(hotelData => hotelData[index]),
            borderColor: getColorForIndex(index),
            backgroundColor: getColorForIndex(index),
            fill: false,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5
        }))
    };
    
    chartToUpdate.options = {
        responsive: true,
        indexAxis: 'y',
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
                    text: metricTitle
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
    
    chartToUpdate.update();
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

function processTrafficData(data, metric) {
    const hotels = [...new Set(data.map(item => item.hotel_name))];
    const dates = [...new Set(data.map(item => item.date))];
    
    const values = hotels.map(hotel => 
        dates.map(date => {
            const entry = data.find(item => 
                item.hotel_name === hotel && 
                item.date === date
            );
            return entry ? getMetricValue(entry, metric) : 0;
        })
    );

    return {
        hotels: hotels,
        dates: dates,
        values: values
    };
}

function processBookingData(data, metric) {
    const hotels = [...new Set(data.map(item => item.hotel_name))];
    const dates = [...new Set(data.map(item => item.date))];
    
    const values = hotels.map(hotel => 
        dates.map(date => {
            const entry = data.find(item => 
                item.hotel_name === hotel && 
                item.date === date
            );
            return entry ? getMetricValue(entry, metric) : 0;
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

// Update the dashboard when metric changes
document.getElementById('trafficMetricSelect').addEventListener('change', updateTrafficChart);
document.getElementById('bookingsMetricSelect').addEventListener('change', updateBookingsChart);
</script>

<?php require_once '../includes/footer.php'; ?> 