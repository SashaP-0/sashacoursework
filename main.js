// The Village Grocers - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add to cart functionality
    window.addToCart = function(itemID) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                itemID: itemID,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Item added to cart!', 'success');
                updateCartCount(data.cartCount);
            } else {
                showNotification(data.message || 'Error adding item to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adding item to cart', 'error');
        });
    };

    // Update cart count in header
    function updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = count;
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create new notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            max-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;

        // Add close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);

        // Add to page
        document.body.appendChild(notification);

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Add error message
                    let errorMsg = field.parentNode.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.style.cssText = 'color: #f44336; font-size: 0.8rem; margin-top: 0.25rem;';
                        field.parentNode.appendChild(errorMsg);
                    }
                    errorMsg.textContent = 'This field is required';
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });

    // Remove error styling on input
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.parentNode.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }

    // Perform search
    function performSearch(query) {
        if (query.length < 2) return;

        fetch(`search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                updateSearchResults(data.results);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    // Update search results
    function updateSearchResults(results) {
        const searchResults = document.querySelector('.search-results');
        if (!searchResults) return;

        if (results.length === 0) {
            searchResults.innerHTML = '<p>No products found</p>';
            return;
        }

        searchResults.innerHTML = results.map(product => `
            <div class="search-result-item">
                <img src="${product.image || 'default.jpg'}" alt="${product.itemname}">
                <div class="search-result-info">
                    <h4>${product.itemname}</h4>
                    <p>£${product.itemprice}</p>
                </div>
            </div>
        `).join('');
    }

    // Quantity increment/decrement
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        const decrementBtn = input.parentNode.querySelector('.quantity-decrement');
        const incrementBtn = input.parentNode.querySelector('.quantity-increment');

        if (decrementBtn) {
            decrementBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    updateQuantity(input);
                }
            });
        }

        if (incrementBtn) {
            incrementBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                input.value = currentValue + 1;
                updateQuantity(input);
            });
        }
    });

    // Update quantity in cart
    function updateQuantity(input) {
        const itemID = input.dataset.itemId;
        const quantity = input.value;

        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                itemID: itemID,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartTotal(data.total);
            }
        })
        .catch(error => {
            console.error('Error updating quantity:', error);
        });
    }

    // Update cart total
    function updateCartTotal(total) {
        const cartTotal = document.querySelector('.cart-total');
        if (cartTotal) {
            cartTotal.textContent = `£${total}`;
        }
    }

    // Initialize tooltips
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });

    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = e.target.dataset.tooltip;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            z-index: 1000;
            pointer-events: none;
        `;

        document.body.appendChild(tooltip);

        const rect = e.target.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';

        e.target.tooltip = tooltip;
    }

    function hideTooltip(e) {
        if (e.target.tooltip) {
            e.target.tooltip.remove();
            e.target.tooltip = null;
        }
    }
});

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(price);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for use in other scripts
window.VillageGrocers = {
    addToCart,
    formatPrice,
    debounce
};
