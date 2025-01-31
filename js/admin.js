let currentOrderId = null;

function initializeAdmin() {
    fetchAdminData();
    
    // Event Listener für Filter und Gruppierung
    document.getElementById('grouping')?.addEventListener('change', updateResults);
    document.getElementById('search')?.addEventListener('input', updateResults);
    document.getElementById('statusFilter')?.addEventListener('change', updateResults);
    document.getElementById('downloadExcel')?.addEventListener('click', downloadExcel);
    
    // Status-Modal Event Listener
    const statusModal = document.getElementById('statusModal');
    const confirmButton = document.getElementById('confirmStatusChange');
    const cancelButton = document.getElementById('cancelStatusChange');

    // Füge Event-Listener für Status-Buttons hinzu
    document.addEventListener('click', function(e) {
        const statusButton = e.target.closest('[data-order-id]');
        if (statusButton) {
            console.log("Status-Button geklickt für Order:", statusButton.dataset.orderId);
            currentOrderId = statusButton.dataset.orderId;
            statusModal?.classList.remove('hidden');
        }
    });

    confirmButton?.addEventListener('click', function() {
        const newStatus = document.getElementById('newStatus').value;
        console.log("Status ändern:", {currentOrderId, newStatus});
        
        if (currentOrderId && newStatus) {
            updateOrderStatus(currentOrderId, newStatus);
            statusModal?.classList.add('hidden');
        }
    });

    cancelButton?.addEventListener('click', function() {
        statusModal?.classList.add('hidden');
        currentOrderId = null;
    });
}

function fetchAdminData() {
    axios.get('admin_panel_grouping.php')
        .then(response => {
            groupedData = response.data;
            updateResults();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Fehler beim Laden der Daten');
        });
}

function updateResults() {
    const grouping = document.getElementById('grouping').value;
    const search = document.getElementById('search').value.toLowerCase();
    const resultsContainer = document.getElementById('results');
    let html = '';

    switch (grouping) {
        case 'user':
            html = generateUserHTML(search);
            break;
        case 'product':
            html = generateProductHTML(search);
            break;
        case 'class':
            html = generateClassHTML(search);
            break;
    }

    if (resultsContainer) {
        resultsContainer.innerHTML = html;
        initializeStatusButtons();
    }
}

function initializeStatusButtons() {
    document.querySelectorAll('.status-button').forEach(button => {
        button.addEventListener('click', function() {
            console.log("Status-Button geklickt für Order:", this.dataset.orderId);
            currentOrderId = this.dataset.orderId;
            document.getElementById('statusModal')?.classList.remove('hidden');
        });
    });
}

function downloadExcel() {
    const grouping = document.getElementById('grouping').value;
    const data = groupedData['groupedBy' + grouping.charAt(0).toUpperCase() + grouping.slice(1)];
    const selectedClass = document.getElementById('classSelect') ? document.getElementById('classSelect').value : null;
    
    let url = 'generate_excel.php?grouping=' + grouping;
    if (selectedClass) {
        url += '&class=' + encodeURIComponent(selectedClass);
    }
    
    axios.post(url, data, {
        responseType: 'blob'
    })
    .then(function (response) {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'Bericht_' + grouping + (selectedClass ? '_' + selectedClass : '') + '.xlsx');
        document.body.appendChild(link);
        link.click();
    })
    .catch(function (error) {
        console.error('Error downloading Excel:', error);
        alert('Fehler beim Herunterladen des Excel-Berichts');
    });
} 