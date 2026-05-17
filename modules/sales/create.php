<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Product.php';
require_once __DIR__ . '/../../classes/Business.php';
require_once __DIR__ . '/../../auth/session.php';
$page_title = 'Record New Sale';
$productModel = new Product($conn);
$businessModel = new Business($conn);

// Get all user's businesses
$businesses = $businessModel->getAll($current_user['id']);
$current_business_id = isset($_GET['business_id']) ? intval($_GET['business_id']) : ($businesses[0]['id'] ?? null);

$products = $productModel->getAll($current_business_id);
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
        <div class="container">
            <div class="flex justify-between align-center mb-3">
                <h2>Record New Sale</h2>
                <a href="index.php?business_id=<?= $current_business_id ?>" class="btn btn-primary">View Sales History</a>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Products Section -->
                <div class="card">
                    <h3>Available Products</h3>
                    <input type="text" id="product-search" class="form-control mb-3" placeholder="Search products...">
                    <div id="product-list" style="max-height:600px; overflow-y:auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 10px;">
                        <?php if (empty($products)): ?>
                            <div class="text-center text-muted" style="padding: 40px 20px;">
                                <p>No products available for this business</p>
                                <p style="font-size: 0.9rem; margin-top: 10px;">Add products to inventory first</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $prod): ?>
                                <div class="product-item" data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>" data-price="<?= $prod['selling_price'] ?>" data-stock="<?= $prod['stock_qty'] ?>" style="padding: 12px; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 10px;">
                                        <div style="flex: 1;">
                                            <b style="display: block; margin-bottom: 4px;"><?= htmlspecialchars($prod['name']) ?></b>
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="color: #10b981; font-weight: 600;">TZS <?= number_format($prod['selling_price'],2) ?></span>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Stock: <?= $prod['stock_qty'] ?></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm add-to-cart" style="white-space: nowrap; padding: 6px 12px;">Add</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="card">
                    <h3>Cart / Order Summary</h3>
                    <form id="sale-form" action="../../handlers/sale_handler.php" method="POST" style="display: flex; flex-direction: column; height: 100%;">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="business_id" value="<?= $current_business_id ?>">
                        <input type="hidden" name="cart_json" id="cart-json">
                        
                        <!-- Cart Items -->
                        <div id="cart-list" style="flex: 1; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 10px; margin-bottom: 15px; overflow-y: auto; min-height: 300px;">
                            <div class="text-center text-muted" style="padding: 60px 20px;">
                                <p>Add products to cart</p>
                            </div>
                        </div>
                        
                        <!-- Totals -->
                        <div style="background: rgba(255,255,255,0.04); padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Subtotal:</span>
                                <span id="subtotal-amount">TZS 0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.1rem; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <span>Total:</span>
                                <span id="order-total" style="color: #10b981;">TZS 0.00</span>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="payment_method" style="display: block; margin-bottom: 8px; font-weight: 500;">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile Money</option>
                            </select>
                        </div>
                        
                        <!-- Buttons -->
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" id="complete-sale-btn" class="btn btn-success" style="flex: 1;" disabled>Complete Sale</button>
                            <button type="reset" class="btn btn-secondary" id="clear-cart-btn" style="flex: 1;">Clear Cart</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
// Sales Module Cart Management
let cart = [];
const CART_STORAGE_KEY = 'bms_sales_cart_' + <?= $current_business_id ?>;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCartFromStorage();
    attachProductListeners();
    attachCartListeners();
    updateCartDisplay();
});

// Attach event listeners to product items
function attachProductListeners() {
    document.querySelectorAll('.product-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart')) {
                addToCart(this);
            }
        });
    });
}

// Add product to cart
function addToCart(productElement) {
    const id = parseInt(productElement.dataset.id);
    const name = productElement.dataset.name;
    const price = parseFloat(productElement.dataset.price);
    const stock = parseInt(productElement.dataset.stock);
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.product_id === id);
    
    if (existingItem) {
        if (existingItem.qty < stock) {
            existingItem.qty += 1;
            existingItem.subtotal = existingItem.qty * existingItem.unit_price;
        } else {
            alert('Cannot add more. Insufficient stock!');
            return;
        }
    } else {
        if (stock > 0) {
            cart.push({
                product_id: id,
                product_name: name,
                unit_price: price,
                qty: 1,
                subtotal: price,
                max_qty: stock
            });
        } else {
            alert('This product is out of stock!');
            return;
        }
    }
    
    saveCartToStorage();
    updateCartDisplay();
}

// Update quantity
function updateQty(productId, newQty) {
    const item = cart.find(i => i.product_id === productId);
    if (item) {
        newQty = parseInt(newQty);
        if (newQty < 1) newQty = 1;
        if (newQty > item.max_qty) {
            alert('Insufficient stock!');
            return;
        }
        item.qty = newQty;
        item.subtotal = item.qty * item.unit_price;
        saveCartToStorage();
        updateCartDisplay();
    }
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    saveCartToStorage();
    updateCartDisplay();
}

// Update cart display
function updateCartDisplay() {
    const cartListDiv = document.getElementById('cart-list');
    const cartJsonInput = document.getElementById('cart-json');
    const completeSaleBtn = document.getElementById('complete-sale-btn');
    
    if (cart.length === 0) {
        cartListDiv.innerHTML = '<div class="text-center text-muted" style="padding: 60px 20px;"><p>Add products to cart</p></div>';
        document.getElementById('order-total').textContent = 'TZS 0.00';
        document.getElementById('subtotal-amount').textContent = 'TZS 0.00';
        cartJsonInput.value = '';
        completeSaleBtn.disabled = true;
        return;
    }
    
    let html = '<table style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">';
    html += '<th style="text-align: left; padding: 8px; font-weight: 600; font-size: 0.85rem;">Product</th>';
    html += '<th style="text-align: center; padding: 8px; font-weight: 600; font-size: 0.85rem;">Qty</th>';
    html += '<th style="text-align: right; padding: 8px; font-weight: 600; font-size: 0.85rem;">Subtotal</th>';
    html += '<th style="text-align: center; padding: 8px; font-weight: 600; font-size: 0.85rem;">Action</th></tr></thead><tbody>';
    
    let total = 0;
    cart.forEach(item => {
        total += item.subtotal;
        html += '<tr style="border-bottom: 1px solid rgba(255,255,255,0.08); hover_effect;">';
        html += '<td style="padding: 8px; font-size: 0.85rem;">' + item.product_name + '</td>';
        html += '<td style="text-align: center; padding: 8px;"><input type="number" min="1" max="' + item.max_qty + '" value="' + item.qty + '" onChange="updateQty(' + item.product_id + ', this.value)" style="width: 50px; padding: 4px; text-align: center; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; background: rgba(255,255,255,0.05); color: inherit;"></td>';
        html += '<td style="text-align: right; padding: 8px; font-weight: 500;">TZS ' + item.subtotal.toFixed(2) + '</td>';
        html += '<td style="text-align: center; padding: 8px;"><button type="button" class="btn btn-danger btn-sm" onClick="removeFromCart(' + item.product_id + ')" style="padding: 4px 8px; font-size: 0.8rem;">Remove</button></td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    cartListDiv.innerHTML = html;
    
    document.getElementById('order-total').textContent = 'TZS ' + total.toFixed(2);
    document.getElementById('subtotal-amount').textContent = 'TZS ' + total.toFixed(2);
    cartJsonInput.value = JSON.stringify(cart);
    completeSaleBtn.disabled = false;
}

// Attach cart listeners
function attachCartListeners() {
    document.getElementById('sale-form').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Please add products to cart before completing sale');
            return false;
        }
    });
    
    document.getElementById('clear-cart-btn').addEventListener('click', function(e) {
        if (confirm('Clear all items from cart?')) {
            cart = [];
            saveCartToStorage();
            updateCartDisplay();
        }
        e.preventDefault();
    });
}

// Search products
document.getElementById('product-search').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.dataset.name.toLowerCase();
        if (name.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// Storage functions
function saveCartToStorage() {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
}

function loadCartFromStorage() {
    const saved = localStorage.getItem(CART_STORAGE_KEY);
    if (saved) {
        try {
            cart = JSON.parse(saved);
        } catch (e) {
            cart = [];
        }
    }
}
</script>

<style>
.product-item:hover {
    background: rgba(255,255,255,0.06) !important;
    border-color: rgba(255,255,255,0.15) !important;
}

.hover_effect:hover {
    background: rgba(255,255,255,0.04);
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>