window.addToCart = function(productId, allowsSizes = false) {
    console.log("addToCart wurde aufgerufen. allows sizes: " + allowsSizes);
    const quantity = document.getElementById('quantity').value;
    const formData = new FormData();
    formData.append('id', productId);
    formData.append('quantity', quantity);

    let sizeName = 'not applicable';

    if (allowsSizes) {
        const sizeSelect = document.getElementById('size');
        console.log("sizeSelect: " + sizeSelect.value);
        if (sizeSelect && sizeSelect.value) {
            console.log("sizes are allowed");
            sizeName = sizeSelect.value;
            formData.append('size_name', sizeName);
        } else {
            showNotification('Bitte wählen Sie eine Größe aus', 'error');
            return;
        }
    }

    console.log('Sending to server:', {
        id: productId,
        quantity: quantity,
        size_name: sizeName
    });

    axios.post('add_to_cart.php', formData)
        .then(function (response) {
            console.log("Vollständige Serverantwort:", response);
            if (response.data && response.data.success) {
                showNotification('Produkt wurde zum Warenkorb hinzugefgt');
                if (response.data.cartCount !== undefined) {
                    updateCartCount(response.data.cartCount);
                }
            } else {
                console.log("Error: ", response.data ? response.data.message : 'Keine Fehlermeldung verfügbar');
                showNotification(response.data && response.data.message ? response.data.message : 'Fehler beim Hinzufügen zum Warenkorb', 'error');
            }
        })
        .catch(function (error) {
            console.error('Axios Error:', error);
            if (error.response) {
                console.log("Fehler-Daten:", error.response.data);
                console.log("Fehler-Status:", error.response.status);
                console.log("Fehler-Header:", error.response.headers);
            } else if (error.request) {
                console.log("Fehler-Request:", error.request);
            } else {
                console.log('Error', error.message);
            }
            showNotification('Fehler beim Hinzufügen zum Warenkorb', 'error');
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

window.initializeCart = function() {
    const cartItems = document.querySelectorAll('li[data-product-id]');
    
    cartItems.forEach(item => {
        const decreaseBtn = item.querySelector('.quantity-decrease');
        const increaseBtn = item.querySelector('.quantity-increase');
        const quantityInput = item.querySelector('.quantity-input');

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', () => updateQuantity(item, -1));
        }
        if (increaseBtn) {
            increaseBtn.addEventListener('click', () => updateQuantity(item, 1));
        }
        if (quantityInput) {
            quantityInput.addEventListener('change', () => updateQuantity(item, 0));
        }
    });
}

function updateQuantity(item, change) {
    const quantityInput = item.querySelector('.quantity-input');
    const subtotalElement = item.querySelector('.item-subtotal');
    const price = parseFloat(item.dataset.price);
    let newQuantity = parseInt(quantityInput.value) + change;
    newQuantity = Math.max(1, newQuantity); // Mindestens 1
    
    updateServer(item.dataset.productId, newQuantity, price, quantityInput, subtotalElement);
}

function updateServer(productId, newQuantity, price, quantityInput, subtotalElement) {
    const [id, size] = productId.split('_');
    axios.post('update_cart.php', {
        product_id: parseInt(id),
        quantity: newQuantity,
        size_name: size || null
    })
    .then(function (response) {
        if (response.data.success) {
            quantityInput.value = newQuantity;
            const subtotal = (price * newQuantity).toFixed(2);
            subtotalElement.textContent = subtotal;
            updateCartTotal();
            updateCartCount(response.data.cartCount);
        } else {
            alert(response.data.message);
        }
    })
    .catch(function (error) {
        console.error('Error updating cart:', error);
        alert('Fehler beim Aktualisieren des Warenkorbs');
    });
}

function updateCartTotal() {
    const cartTotal = document.getElementById('cart-total');
    if (cartTotal) {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(subtotal => {
            total += parseFloat(subtotal.textContent);
        });
        cartTotal.textContent = total.toFixed(2);
    }
}

function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

function updateCartCountDisplay(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline-block' : 'none'; // Immer anzeigen
    }
}

function initializeNavbar() {
    document.addEventListener('DOMContentLoaded', function() {
        axios.get('get_cart_count.php')
            .then(function (response) {
                if (response.data.success) {
                    updateCartCountDisplay(response.data.count);
                }
            })
            .catch(function (error) {
                console.error('Fehler beim Abrufen der Warenkorbanzahl:', error);
            });
    });
}

function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
}

function initializeIndex() {
    gsap.registerPlugin(ScrollTrigger);

    // Hero-Animationen
    gsap.to("#hero-title", {opacity: 1, y: 0, duration: 1, delay: 0.5});
    gsap.to("#hero-slogan", {opacity: 1, y: 0, duration: 1, delay: 0.7});
    gsap.to("#hero-cta", {opacity: 1, y: 0, duration: 1, delay: 0.9});

    // Produkt-Animationen
    gsap.to("#products-title", {
        scrollTrigger: {
            trigger: "#products-title",
            start: "top 80%",
        },
        opacity: 1,
        y: 0,
        duration: 1
    });

    gsap.utils.toArray(".product-card").forEach((card, index) => {
        gsap.to(card, {
            scrollTrigger: {
                trigger: card,
                start: "top 80%",
            },
            opacity: 1,
            y: 0,
            duration: 0.5,
            delay: index * 0.1
        });
    });

    // Feature-Animationen
    gsap.to("#features-title", {
        scrollTrigger: {
            trigger: "#features-title",
            start: "top 80%",
        },
        opacity: 1,
        y: 0,
        duration: 1
    });

    gsap.utils.toArray(".feature-card").forEach((card, index) => {
        gsap.to(card, {
            scrollTrigger: {
                trigger: card,
                start: "top 80%",
            },
            opacity: 1,
            y: 0,
            duration: 0.5,
            delay: index * 0.2
        });
    });

    // Initialisiere Vanilla Tilt für 3D-Hover-Effekte
    VanillaTilt.init(document.querySelectorAll(".product-card, .feature-card"), {
        max: 25,
        speed: 400,
        glare: true,
        "max-glare": 0.5,
    });
}

let groupedData = {}

window.initializeAdmin = function() {
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

function generateUserHTML(search) {
    const statusFilter = document.getElementById('statusFilter').value;
    let html = '<div class="overflow-x-auto">';
    html += '<table class="min-w-full divide-y divide-gray-200">';
    html += '<thead class="bg-gray-50"><tr>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benutzer</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klasse</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bestellungen & Status</th>';
    html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gesamtausgaben</th>';
    html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

    for (const [userId, userData] of Object.entries(groupedData.groupedByUser)) {
        if (userData.email.toLowerCase().includes(search.toLowerCase())) {
            const filteredOrders = userData.orders.filter(order => 
                !statusFilter || order.status_id.toString() === statusFilter
            );

            if (filteredOrders.length > 0) {
                html += `<tr>
                    <td class="px-6 py-4">${userData.email}</td>
                    <td class="px-6 py-4">${userData.class_name}</td>
                    <td class="px-6 py-4">
                        <div class="space-y-2">
                            ${filteredOrders.map(order => `
                                <div class="flex items-center gap-2">
                                    <span>Bestellung #${order.order_id} (${new Date(order.date).toLocaleDateString()})</span>
                                    <button 
                                        type="button"
                                        data-order-id="${order.order_id}"
                                        class="px-3 py-1 rounded text-white text-sm status-button"
                                        style="background-color: ${order.status_color}">
                                        ${order.status_name}
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </td>
                    <td class="px-6 py-4">€${userData.total_spent.toFixed(2)}</td>
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

    for (const [productKey, productData] of Object.entries(groupedData.groupedByProduct)) {
        if (productData.name.toLowerCase().includes(search.toLowerCase())) {
            html += `<tr>
                <td class="px-6 py-4 whitespace-nowrap">${productData.name}</td>
                <td class="px-6 py-4 whitespace-nowrap">${productData.size}</td>
                <td class="px-6 py-4 whitespace-nowrap">${productData.total_quantity}</td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(productData.users).map(([userId, user]) => `
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

    for (const [className, classData] of Object.entries(groupedData.groupedByClass)) {
        const displayClassName = className === 'Lehrer' ? 'Lehrer' : className;
        if (displayClassName.toLowerCase().includes(search.toLowerCase())) {
            html += `<tr>
                <td class="px-6 py-4 whitespace-nowrap">${displayClassName}</td>
                <td class="px-6 py-4 whitespace-nowrap">€${classData.total_spent.toFixed(2)}</td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(classData.users).map(([userId, user]) => `
                            <li>${user.email} - €${user.total_spent.toFixed(2)}</li>
                        `).join('')}
                    </ul>
                </td>
                <td class="px-6 py-4">
                    <ul class="list-disc list-inside">
                        ${Object.entries(classData.products).map(([productKey, product]) => `
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
function updateChart(grouping) {
    const ctx = document.getElementById('orderChart').getContext('2d');
    let data, labels;

    switch (grouping) {
        case 'user':
            data = Object.values(groupedData.groupedByUser).map(user => user.total_spent);
            labels = Object.values(groupedData.groupedByUser).map(user => user.email);
            break;
        case 'product':
            data = Object.values(groupedData.groupedByProduct).map(product => product.total_quantity);
            labels = Object.values(groupedData.groupedByProduct).map(product => product.name);
            break;
        case 'class':
            data = Object.values(groupedData.groupedByClass).map(classData => classData.total_spent);
            labels = Object.keys(groupedData.groupedByClass);
            break;
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: grouping === 'product' ? 'Verkaufte Menge' : 'Gesamtbetrag',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
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

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('li[data-product-id]')) {
        initializeCart();
    }
    
    if (document.getElementById('grouping')) {
        initializeAdmin();
    }
    initializeNavbar();
});

let currentOrderId = null;

function changeStatus(orderId) {
    currentOrderId = orderId;
    const modal = document.getElementById('statusModal');
    modal.classList.remove('hidden');
}

function initializeStatusModal() {
    const modal = document.getElementById('statusModal');
    const cancelBtn = document.getElementById('cancelStatusChange');
    const confirmBtn = document.getElementById('confirmStatusChange');

    cancelBtn.onclick = () => {
        modal.classList.add('hidden');
        currentOrderId = null;
    };

    confirmBtn.onclick = () => {
        const newStatus = document.getElementById('newStatus');
        if (currentOrderId) {
            updateOrderStatus(currentOrderId, newStatus.value);
        }
        modal.classList.add('hidden');
    };

    // Schließen bei Klick außerhalb des Modals
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.classList.add('hidden');
            currentOrderId = null;
        }
    };
}

function updateOrderStatus(orderId, statusId) {
    console.log("Update Status aufgerufen:", {orderId, statusId});
    
    const formData = new FormData();
    formData.append('orderId', orderId);
    formData.append('statusId', statusId);
    
    axios.post('update_order_status.php', formData)
        .then(response => {
            console.log("Server-Antwort:", response.data);
            if (response.data.success) {
                const statusButton = document.querySelector(`[data-order-id="${orderId}"]`);
                console.log("Status-Button gefunden:", !!statusButton);
                if (statusButton) {
                    statusButton.textContent = response.data.newStatus.name;
                    statusButton.style.backgroundColor = response.data.newStatus.color;
                }
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('Fehler beim Aktualisieren des Status');
        });
}
