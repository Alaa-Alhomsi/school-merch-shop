function generateUserHTML(search) {
    const statusFilter = document.getElementById('statusFilter').value;
    let html = '<div class="overflow-x-auto">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestellnummer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtbetrag</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    let hasOrders = false;

    for (const [userId, userData] of Object.entries(groupedData.groupedByUser)) {
        if (userData.email.toLowerCase().includes(search.toLowerCase())) {
            userData.orders.forEach(order => {
                if (!statusFilter || order.status_id == statusFilter) {
                    hasOrders = true;
                    html += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">#${order.order_id}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${new Date(order.date).toLocaleString('de-DE')}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${userData.email}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${userData.class_name}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="changeStatus(${order.order_id})"
                                        data-order-id="${order.order_id}"
                                        class="px-3 py-1 rounded text-white text-sm"
                                        style="background-color: ${order.status_color}">
                                    ${order.status_name}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">€${userData.total_spent.toFixed(2)}</td>
                        </tr>
                    `;
                }
            });
        }
    }

    if (!hasOrders) {
        html += '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Keine Bestellungen gefunden</td></tr>';
    }

    html += '</tbody></table></div>';
    return html;
}

function initializeAdmin() {
    const searchInput = document.getElementById('searchInput');
    const groupingSelect = document.getElementById('groupingSelect');
    const statusFilter = document.getElementById('statusFilter');
    const resultsDiv = document.getElementById('results');

    // Lade die Daten und zeige standardmäßig neue Bestellungen
    fetchAdminData().then(() => {
        updateResults();
    });

    // Event Listener für Filter und Suche
    searchInput.addEventListener('input', updateResults);
    groupingSelect.addEventListener('change', updateResults);
    statusFilter.addEventListener('change', updateResults);
    
    initializeStatusModal();
}
