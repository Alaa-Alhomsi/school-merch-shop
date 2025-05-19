let groupedData = {};
let currentOrderId = null;

function initializeAdmin() {
    fetchAdminData();
    
    // Event Listener für Filter und Gruppierung
    document.getElementById('grouping')?.addEventListener('change', function() {
        fetchAdminData(); // Daten neu holen bei Gruppierungsänderung
    });
    
    document.getElementById('statusFilter')?.addEventListener('change', function() {
        fetchAdminData(); // Daten neu holen bei Status-Änderung
    });
    
    document.getElementById('search')?.addEventListener('input', function() {
        updateResults(); // Nur Anzeige aktualisieren bei Suche
    });
    
    document.getElementById('downloadExcel')?.addEventListener('click', downloadExcel);
    
    // Status-Modal Event Listener
    const statusModal = document.getElementById('statusModal');
    const confirmButton = document.getElementById('confirmStatusChange');
    const cancelButton = document.getElementById('cancelStatusChange');

    confirmButton?.addEventListener('click', function() {
        const newStatus = document.getElementById('newStatus').value;
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
    const statusFilter = document.getElementById('statusFilter').value;
    const grouping = document.getElementById('grouping').value;
    
    // URL mit Parametern erstellen
    let url = 'admin_panel_grouping.php';
    if (statusFilter) {
        url += `?status=${statusFilter}`;
    }
    if (grouping) {
        url += (statusFilter ? '&' : '?') + `grouping=${grouping}`;
    }

    axios.get(url)
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
        case 'order':
            html = generateOrderHTML(search);
            break;
    }

    if (resultsContainer) {
        resultsContainer.innerHTML = html;
        initializeStatusButtons();
    }
}

function generateUserHTML(search) {
    const statusFilter = document.getElementById('statusFilter').value;
    let html = '<div class="overflow-x-auto">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestellungen & Status</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    for (const [userId, userData] of Object.entries(groupedData.groupedByUser || {})) {
        if (userData.email.toLowerCase().includes(search.toLowerCase())) {
            const orders = Object.entries(userData.orders || {});
            const filteredOrders = orders.filter(([orderId, order]) => 
                !statusFilter || order.status_id.toString() === statusFilter
            );

            if (filteredOrders.length > 0) {
                html += `<tr>
                    <td class="px-6 py-4">${userData.email}</td>
                    <td class="px-6 py-4">${userData.class_name}</td>
                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            ${filteredOrders.map(([orderId, order]) => `
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span>Bestellung #${orderId} (${new Date(order.date).toLocaleDateString()})</span>
                                        <button 
                                            type="button"
                                            data-order-id="${orderId}"
                                            class="px-3 py-1 rounded text-white text-sm status-button"
                                            style="background-color: ${order.status_color}">
                                            ${order.status_name}
                                        </button>
                                    </div>
                                    <div class="ml-4">
                                        <strong>Produkte:</strong>
                                        <ul class="list-disc list-inside">
                                            ${order.products.map(product => `
                                                <li>${product.product_name} (Größe: ${product.size}, Menge: ${product.quantity}) - €${product.total_price.toFixed(2)}</li>
                                            `).join('')}
                                        </ul>
                                        <div class="mt-2">
                                            <strong>Gesamtpreis: €${order.total_price.toFixed(2)}</strong>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </td>
                </tr>`;
            }
        }
    }

    html += '</tbody></table></div>';
    return html;
}

function generateProductHTML(search) {
    let html = '<div class="overflow-hidden">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Größe</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtverkäufe</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Käufer</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    for (const [productKey, productData] of Object.entries(groupedData.groupedByProduct || {})) {
        if (productData.name.toLowerCase().includes(search.toLowerCase())) {
            html += `<tr>
                <td class="px-6 py-4 whitespace-nowrap">${productData.name}</td>
                <td class="px-6 py-4 whitespace-nowrap">${productData.size}</td>
                <td class="px-6 py-4 whitespace-nowrap">${productData.total_quantity}</td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(productData.users || {}).map(([userId, user]) => `
                            <li>
                                ${user.email} (${user.class_name}) - ${user.quantity}x
                                <ul class="list-circle list-inside ml-4">
                                    ${user.orders.map(order => `
                                        <li>Bestellung #${order.order_id} am ${new Date(order.date).toLocaleDateString()} - ${order.quantity}x</li>
                                    `).join('')}
                                </ul>
                            </li>
                        `).join('')}
                    </ul>
                </td>
            </tr>`;
        }
    }

    html += '</tbody></table></div>';
    return html;
}

function generateClassHTML(search) {
    let html = '<div class="overflow-hidden">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtausgaben</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkte</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    for (const [className, classData] of Object.entries(groupedData.groupedByClass || {})) {
        const displayClassName = className === 'Lehrer' ? 'Lehrer' : className;
        if (displayClassName.toLowerCase().includes(search.toLowerCase())) {
            html += `<tr>
                <td class="px-6 py-4 whitespace-nowrap">${displayClassName}</td>
                <td class="px-6 py-4 whitespace-nowrap">€${classData.total_spent.toFixed(2)}</td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(classData.users || {}).map(([userId, user]) => `
                            <li>${user.email} - €${user.total_spent.toFixed(2)}</li>
                        `).join('')}
                    </ul>
                </td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(classData.products || {}).map(([productKey, product]) => `
                            <li>${product.name} (Größe: ${product.size}) - ${product.quantity}x</li>
                        `).join('')}
                    </ul>
                </td>
            </tr>`;
        }
    }

    html += '</tbody></table></div>';
    return html;
}

function generateOrderHTML(search) {
    const statusFilter = document.getElementById('statusFilter').value;
    let html = '<div class="overflow-x-auto">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestellungs-ID</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkte</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtpreis</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    for (const [orderId, orderData] of Object.entries(groupedData.groupedByOrder || {})) {
        if (statusFilter && orderData.status_id.toString() !== statusFilter) {
            continue;
        }

        const matchesSearch = 
            orderId.toString().includes(search) ||
            orderData.email.toLowerCase().includes(search) ||
            orderData.class_name.toLowerCase().includes(search);

        if (matchesSearch) {
            html += `<tr>
                <td class="px-6 py-4">${orderId}</td>
                <td class="px-6 py-4">${new Date(orderData.created_at).toLocaleDateString()}</td>
                <td class="px-6 py-4">${orderData.email}</td>
                <td class="px-6 py-4">${orderData.class_name}</td>
                <td class="px-6 py-4">
                    <button 
                        type="button"
                        data-order-id="${orderId}"
                        class="px-3 py-1 rounded text-white text-sm status-button"
                        style="background-color: ${orderData.status_color}">
                        ${orderData.status_name}
                    </button>
                </td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(orderData.products || {}).map(([productKey, product]) => `
                            <li>${product.name} (Größe: ${product.size}, Menge: ${product.quantity}) - €${product.total_price.toFixed(2)}</li>
                        `).join('')}
                    </ul>
                </td>
                <td class="px-6 py-4">€${orderData.total_price.toFixed(2)}</td>
            </tr>`;
        }
    }

    html += '</tbody></table></div>';
    return html;
}

function initializeStatusButtons() {
    document.querySelectorAll('.status-button').forEach(button => {
        button.addEventListener('click', function() {
            currentOrderId = this.dataset.orderId;
            document.getElementById('statusModal')?.classList.remove('hidden');
        });
    });
}

function updateOrderStatus(orderId, statusId) {
    const formData = new FormData();
    formData.append('orderId', orderId);
    formData.append('statusId', statusId);
    
    axios.post('update_order_status.php', formData)
        .then(response => {
            if (response.data.success) {
                fetchAdminData(); // Daten neu laden
                showNotification('Status wurde erfolgreich aktualisiert', 'success');
            } else {
                showNotification(response.data.message || 'Fehler beim Aktualisieren des Status', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            showNotification('Fehler beim Aktualisieren des Status', 'error');
        });
}

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.textContent = message;
        notification.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
        notification.classList.add(type === 'success' ? 'bg-green-500' : 'bg-red-500');
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
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