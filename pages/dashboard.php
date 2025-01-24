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
                            <label>Hotel:</label>
                            <select class="form-control" id="trafficHotelSelect">
                                <!-- Will be populated dynamically -->
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
                            <label>Hotel:</label>
                            <select class="form-control" id="bookingsHotelSelect">
                                <!-- Will be populated dynamically -->
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

    // Populate hotel selects
    populateHotelSelects();
    
    // Initialize charts
    const chartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'start'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Dates'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Values'
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

    // Initial load
    updateTrafficChart();
    updateBookingsChart();
}

function populateHotelSelects() {
    fetch('../api/hotels.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.hotels.length > 0) {
                const trafficSelect = document.getElementById('trafficHotelSelect');
                const bookingsSelect = document.getElementById('bookingsHotelSelect');
                
                // Clear existing options
                trafficSelect.innerHTML = '<option value="">Select a Hotel</option>';
                bookingsSelect.innerHTML = '<option value="">Select a Hotel</option>';
                
                // Add new options
                data.hotels.forEach(hotel => {
                    const trafficOption = new Option(hotel.hotel_name, hotel.id);
                    const bookingsOption = new Option(hotel.hotel_name, hotel.id);
                    trafficSelect.add(trafficOption);
                    bookingsSelect.add(bookingsOption);
                });

                // Select first hotel by default
                if (data.hotels.length > 0) {
                    trafficSelect.value = data.hotels[0].id;
                    bookingsSelect.value = data.hotels[0].id;
                    
                    // Trigger initial chart updates
                    updateTrafficChart();
                    updateBookingsChart();
                }
            } else {
                console.error('No hotels found or error in response:', data);
            }
        })
        .catch(error => {
            console.error('Error loading hotels:', error);
        });
}

function updateTrafficChart() {
    const startDate = document.getElementById('trafficStartDate').value;
    const endDate = document.getElementById('trafficEndDate').value;
    const hotelId = document.getElementById('trafficHotelSelect').value;
    
    fetchAndUpdateChart('traffic', startDate, endDate, hotelId);
}

function updateBookingsChart() {
    const startDate = document.getElementById('bookingsStartDate').value;
    const endDate = document.getElementById('bookingsEndDate').value;
    const hotelId = document.getElementById('bookingsHotelSelect').value;
    
    fetchAndUpdateChart('bookings', startDate, endDate, hotelId);
}

function fetchAndUpdateChart(type, startDate, endDate, hotelId) {
    if (!hotelId) {
        console.log('No hotel selected');
        return;
    }

    fetch(`../api/dashboard.php?type=${type}&start_date=${startDate}&end_date=${endDate}&hotel_id=${hotelId}`)
        .then(response => response.json())
        .then(data => {
            console.log(`${type} data received:`, data); // Debug log
            if (data.success) {
                updateChartData(type, data, hotelId);
            } else {
                console.error(`Error loading ${type} data:`, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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

function updateChartData(type, data, hotelId) {
    const chartToUpdate = type === 'traffic' ? trafficChart : bookingsChart;
    const processedData = type === 'traffic' 
        ? processTrafficData(data.traffic || [], hotelId) 
        : processBookingData(data.bookings || [], hotelId);
    
    chartToUpdate.data = processedData;
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

function processTrafficData(data, hotelId) {
    if (!data || data.length === 0) {
        console.log('No traffic data available');
        return {
            labels: [],
            datasets: []
        };
    }

    // Get all dates for this hotel
    const dates = [...new Set(data.map(item => item.date))].sort();
    
    // Define the metrics we want to show
    const metrics = [
        { key: 'expected_traffic', label: 'Expected Traffic', color: 'rgb(255, 99, 132)' },
        { key: 'new_users', label: 'New Users', color: 'rgb(54, 162, 235)' },
        { key: 'bookings', label: 'Bookings', color: 'rgb(75, 192, 192)' }
    ];

    // Create a dataset for each metric
    const datasets = metrics.map(metric => ({
        label: metric.label,
        data: dates.map(date => {
            const entry = data.find(item => item.date === date);
            return entry ? entry[metric.key] || 0 : 0;
        }),
        borderColor: metric.color,
        backgroundColor: metric.color,
        fill: false,
        tension: 0.4
    }));

    return {
        labels: dates,
        datasets: datasets
    };
}

function processBookingData(data, hotelId) {
    if (!data || data.length === 0) {
        console.log('No booking data available');
        return {
            labels: [],
            datasets: []
        };
    }

    // Get all dates for this hotel
    const dates = [...new Set(data.map(item => item.date))].sort();
    
    // Define the metrics we want to show
    const metrics = [
        { key: 'number_of_rooms', label: 'Number of Rooms', color: 'rgb(255, 99, 132)' },
        { key: 'booking_target', label: 'Booking Target', color: 'rgb(54, 162, 235)' },
        { key: 'actual_bookings', label: 'Actual Bookings', color: 'rgb(75, 192, 192)' },
        { key: 'booked_nights', label: 'Booked Nights', color: 'rgb(255, 206, 86)' }
    ];

    // Create a dataset for each metric
    const datasets = metrics.map(metric => ({
        label: metric.label,
        data: dates.map(date => {
            const entry = data.find(item => item.date === date);
            return entry ? entry[metric.key] || 0 : 0;
        }),
        borderColor: metric.color,
        backgroundColor: metric.color,
        fill: false,
        tension: 0.4
    }));

    return {
        labels: dates,
        datasets: datasets
    };
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', initializeCharts);

// Update the dashboard when metric changes
document.getElementById('trafficHotelSelect').addEventListener('change', updateTrafficChart);
document.getElementById('bookingsHotelSelect').addEventListener('change', updateBookingsChart);
</script>

<?php require_once '../includes/footer.php'; ?> 