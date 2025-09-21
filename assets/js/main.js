// Theme color management
function applyTheme() {
    const theme = JSON.parse(localStorage.getItem('theme')) || {
        primary: '#3B82F6',
        secondary: '#10B981',
        accent: '#8B5CF6'
    };
    
    document.documentElement.style.setProperty('--primary-color', theme.primary);
    document.documentElement.style.setProperty('--secondary-color', theme.secondary);
    document.documentElement.style.setProperty('--accent-color', theme.accent);
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    applyTheme();
    
    // Theme color picker functionality
    const colorPickers = document.querySelectorAll('.color-picker');
    colorPickers.forEach(picker => {
        picker.addEventListener('change', function() {
            const theme = JSON.parse(localStorage.getItem('theme')) || {
                primary: '#3B82F6',
                secondary: '#10B981',
                accent: '#8B5CF6'
            };
            
            theme[this.dataset.color] = this.value;
            localStorage.setItem('theme', JSON.stringify(theme));
            applyTheme();
        });
    });
    
    // Live class functionality
    const joinClassBtn = document.getElementById('join-class-btn');
    if (joinClassBtn) {
        joinClassBtn.addEventListener('click', function() {
            alert('Joining live class... (This would connect to WebRTC in a real implementation)');
            this.textContent = 'Connected';
            this.classList.remove('btn');
            this.classList.add('btn-secondary');
        });
    }
    
    // QR scanner simulation
    const qrScanner = document.getElementById('qr-scanner');
    if (qrScanner) {
        qrScanner.addEventListener('click', function() {
            alert('QR Scanner would open here... (Simulating check-in)');
            document.getElementById('attendance-status').textContent = 'Checked in successfully!';
            document.getElementById('attendance-status').className = 'alert alert-success';
        });
    }
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'red';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Search functionality
function searchContent() {
    const searchInput = document.getElementById('search-input');
    const searchTerm = searchInput.value.toLowerCase();
    const cards = document.querySelectorAll('.course-card, .ebook-card');
    
    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const description = card.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Initialize search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', searchContent);
    }
});