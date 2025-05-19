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

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('li[data-product-id]')) {
        initializeCart();
    }
    initializeNavbar();
});
