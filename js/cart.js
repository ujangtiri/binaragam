// Shopping Cart Management for Bina Ragam Shop

class ShoppingCart {
    constructor() {
        this.items = this.loadCart();
        this.updateCartDisplay();
    }
    
    loadCart() {
        const saved = localStorage.getItem('binaragam-cart');
        return saved ? JSON.parse(saved) : [];
    }
    
    saveCart() {
        localStorage.setItem('binaragam-cart', JSON.stringify(this.items));
    }
    
    addItem(product) {
        const existingItem = this.items.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.items.push({
                ...product,
                quantity: 1
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
    }
    
    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCartDisplay();
        this.showNotification('Produk dihapus dari keranjang', 'info');
    }
    
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }
    
    clearCart() {
        this.items = [];
        this.saveCart();
        this.updateCartDisplay();
    }
    
    getTotalItems() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }
    
    getTotalPrice() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    updateCartDisplay() {
        const cartCount = document.getElementById('cart-count');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        if (cartCount) cartCount.textContent = this.getTotalItems();
        if (cartCountMobile) cartCountMobile.textContent = this.getTotalItems();
        
        // Update cart page if it exists
        if (document.getElementById('cart-items')) {
            this.renderCartItems();
        }
    }
    
    renderCartItems() {
        const cartItemsContainer = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const emptyCart = document.getElementById('empty-cart');
        
        if (!cartItemsContainer) return;
        
        if (this.items.length === 0) {
            cartItemsContainer.innerHTML = '';
            if (emptyCart) emptyCart.classList.remove('hidden');
            if (cartTotal) cartTotal.parentElement.classList.add('hidden');
            return;
        }
        
        if (emptyCart) emptyCart.classList.add('hidden');
        if (cartTotal) cartTotal.parentElement.classList.remove('hidden');
        
        cartItemsContainer.innerHTML = this.items.map(item => `
            <div class="flex items-center space-x-4 bg-white p-4 rounded-lg shadow-sm">
                <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">${item.name}</h3>
                    <p class="text-gray-600 text-sm">Rp ${item.price.toLocaleString('id-ID')}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})" 
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <i data-lucide="minus" class="w-4 h-4"></i>
                    </button>
                    <span class="w-8 text-center font-medium">${item.quantity}</span>
                    <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})" 
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900">Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</p>
                    <button onclick="cart.removeItem(${item.id})" class="text-red-500 hover:text-red-700 text-sm">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        if (cartTotal) {
            cartTotal.textContent = `Rp ${this.getTotalPrice().toLocaleString('id-ID')}`;
        }
        
        // Reinitialize icons
        lucide.createIcons();
    }
    
    showNotification(message, type = 'success') {
        // Use SweetAlert2 for notifications
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: type,
            title: message,
            background: type === 'success' ? '#F5B700' : '#1C1C1C',
            color: type === 'success' ? '#1C1C1C' : '#FFFFFF'
        });
    }
}

// Initialize cart
const cart = new ShoppingCart();

// Global function for adding to cart
function addToCart(id, name, price, image) {
    const product = { id, name, price, image };
    cart.addItem(product);
}

// Global function for removing from cart
function removeFromCart(id) {
    cart.removeItem(id);
}

// Global function for updating quantity
function updateQuantity(id, quantity) {
    cart.updateQuantity(id, quantity);
}

// Export for use in other files
window.ShoppingCart = ShoppingCart;
window.cart = cart;