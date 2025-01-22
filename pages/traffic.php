<?php require_once '../includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add Traffic Data</h5>
            </div>
            <div class="card-body">
                <form id="trafficForm">
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
                            <small class="text-muted">Format: Hotel name | Expected traffic | New users | Number of bookings</small>
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
                                <th>Expected Traffic</th>
                                <th>New Users</th>
                                <th>Number of Bookings</th>
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

document.getElementById('trafficForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitData();
});

function previewData() {
    const rawData = document.getElementById('tableData').value;
    const rows = rawData.split('\n').filter(row => row.trim() !== '');
    const tbody = document.querySelector('#previewTable tbody');
    tbody.innerHTML = '';

    rows.forEach(row => {
        const columns = row.split('\t');
        if (columns.length >= 2) {  // Changed from 4 to 2 since we have 3 columns now
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${columns[0] || ''}</td>
                <td>${columns[1] || '0'}</td>
                <td>${columns[2] || '0'}</td>
                <td>${columns[3] || '0'}</td>
            `;
            tbody.appendChild(tr);
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
        return trimmedRow !== '' && !trimmedRow.endsWith('\t');  // Skip empty rows and rows ending with tab
    });

    const formattedData = rows.map(row => {
        const columns = row.split('\t');
        return {
            hotel_name: columns[0].trim(),
            expected_traffic: parseInt(columns[1]) || 0,
            new_users: parseInt(columns[2]) || 0,
            bookings: 0  // Since this column isn't in your data, we'll set it to 0
        };
    });

    // Log the formatted data to console for debugging
    console.log('Formatted Data:', formattedData);

    fetch('../api/traffic.php', {
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
    document.getElementById('trafficForm').reset();
    document.querySelector('#previewTable tbody').innerHTML = '';
}
</script>

<?php require_once '../includes/footer.php'; ?> 