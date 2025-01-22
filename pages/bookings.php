<?php require_once '../includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Booking Data</h5>
            </div>
            <div class="card-body">
                <form id="bookingForm">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Select Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="tableData" class="form-label">Paste Table Data</label>
                            <textarea class="form-control" id="tableData" rows="10" 
                                    placeholder="Paste your data here from Google Sheets..."></textarea>
                            <small class="text-muted">Format: Hotel name | Number of rooms | Booking target | Actual bookings | Booked nights</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Submit Data</button>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Data Preview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="previewTable">
                        <thead>
                            <tr>
                                <th>Hotel Name</th>
                                <th>Number of Rooms</th>
                                <th>Booking Target</th>
                                <th>Actual Bookings</th>
                                <th>Booked Nights</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tableData').addEventListener('paste', function(e) {
    setTimeout(() => {
        previewData();
    }, 100);
});

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitData();
});

function extractNumber(str) {
    if (!str) return 0;
    // Extract the first number found in the string
    const match = str.toString().match(/\d+/);
    return match ? parseFloat(match[0]) : 0;
}

function previewData() {
    const rawData = document.getElementById('tableData').value;
    const rows = rawData.split('\n').filter(row => row.trim() !== '');
    const tbody = document.querySelector('#previewTable tbody');
    tbody.innerHTML = '';

    rows.forEach(row => {
        const columns = row.split('\t');
        if (columns.length >= 2) {
            // Extract numbers from each column
            const numericValues = columns.slice(1).map(val => extractNumber(val));
            const hasNumericValue = numericValues.some(val => val > 0);
            
            if (hasNumericValue) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${columns[0] || ''}</td>
                    <td>${numericValues[0] || '0'}</td>
                    <td>${numericValues[1] || '0'}</td>
                    <td>${numericValues[2] || '0'}</td>
                    <td>${numericValues[3] || '0'}</td>
                `;
                tbody.appendChild(tr);
            }
        }
    });
}

function submitData() {
    const date = document.getElementById('date').value;
    const rawData = document.getElementById('tableData').value;
    
    if (!date || !rawData) {
        alert('Please fill in all required fields');
        return;
    }

    const rows = rawData.split('\n').filter(row => {
        const trimmedRow = row.trim();
        if (trimmedRow === '' || trimmedRow.endsWith('\t')) return false;
        
        const columns = trimmedRow.split('\t');
        // Check if any column contains a number
        return columns.slice(1).some(val => /\d+/.test(val));
    });

    const formattedData = rows.map(row => {
        const columns = row.split('\t');
        return {
            hotel_name: columns[0].trim(),
            number_of_rooms: extractNumber(columns[1]),
            booking_target: extractNumber(columns[2]),
            actual_bookings: extractNumber(columns[3]),
            booked_nights: extractNumber(columns[4])
        };
    }).filter(row => {
        // Additional validation: ensure at least one numeric value is non-zero
        return row.number_of_rooms > 0 || 
               row.booking_target > 0 || 
               row.actual_bookings > 0 || 
               row.booked_nights > 0;
    });

    // Log the formatted data to console for debugging
    console.log('Formatted Data:', formattedData);

    if (formattedData.length === 0) {
        alert('No valid data to submit. Please check your input.');
        return;
    }

    fetch('../api/bookings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            date: date,
            data: formattedData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Data saved successfully!');
            clearForm();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the data');
    });
}

function clearForm() {
    document.getElementById('bookingForm').reset();
    document.querySelector('#previewTable tbody').innerHTML = '';
}
</script>

<?php require_once '../includes/footer.php'; ?> 