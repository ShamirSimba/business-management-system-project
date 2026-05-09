// Form Validations

function validateLoginForm() {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    
    clearErrors();
    
    if (!email) {
        showError('email', 'Email is required');
        return false;
    }
    
    if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email');
        return false;
    }
    
    if (!password) {
        showError('password', 'Password is required');
        return false;
    }
    
    if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        return false;
    }
    
    return true;
}

function validateRegisterForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirm_password = document.getElementById('confirm_password').value.trim();
    
    clearErrors();
    
    if (!name) {
        showError('name', 'Name is required');
        return false;
    }
    
    if (!email) {
        showError('email', 'Email is required');
        return false;
    }
    
    if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email');
        return false;
    }
    
    if (!password) {
        showError('password', 'Password is required');
        return false;
    }
    
    if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        return false;
    }
    
    if (!confirm_password) {
        showError('confirm_password', 'Please confirm password');
        return false;
    }
    
    if (password !== confirm_password) {
        showError('confirm_password', 'Passwords do not match');
        return false;
    }
    
    return true;
}

function validateSaleForm() {
    const cart = JSON.parse(document.getElementById('cart-json').value || '[]');
    
    clearErrors();
    
    if (!cart || cart.length === 0) {
        showError('cart', 'Cart is empty. Add at least one item');
        return false;
    }
    
    return true;
}

function validateProductForm() {
    const costPrice = parseFloat(document.getElementById('cost_price').value);
    const sellingPrice = parseFloat(document.getElementById('selling_price').value);
    const stockQty = parseInt(document.getElementById('stock_qty').value);
    
    clearErrors();
    
    if (costPrice <= 0) {
        showError('cost_price', 'Cost price must be greater than 0');
        return false;
    }
    
    if (sellingPrice <= 0) {
        showError('selling_price', 'Selling price must be greater than 0');
        return false;
    }
    
    if (sellingPrice < costPrice) {
        showError('selling_price', 'Selling price cannot be less than cost price');
        return false;
    }
    
    if (stockQty < 0) {
        showError('stock_qty', 'Stock quantity cannot be negative');
        return false;
    }
    
    return true;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-danger';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }
}

function clearErrors() {
    document.querySelectorAll('.form-control').forEach(field => {
        field.classList.remove('error');
    });
    document.querySelectorAll('.error-message').forEach(msg => {
        msg.remove();
    });
}