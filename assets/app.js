/*
 * Knowledge Learning - Main JavaScript File
 * Using Symfony AssetMapper
 */

// Import Stimulus Bootstrap (Symfony UX)
import './stimulus_bootstrap.js';

// Import styles
import './styles/app.css';

// Import Bootstrap JS
import 'bootstrap';

console.log('Knowledge Learning - Assets loaded!  🎉');

// ========================================
// DOM Ready - All interactions
// ========================================
document.addEventListener('DOMContentLoaded', function() {

    // ====================================
    // 1. Auto-hide alerts after 5 seconds
    // ====================================
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            // Vérifier que Bootstrap est chargé
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                // Fallback si Bootstrap n'est pas chargé
                alert.style.display = 'none';
            }
        }, 5000);
    });

    // ====================================
    // 2. Confirm before buying
    // ====================================
    const buyButtons = document.querySelectorAll('a[href*="/acheter"]');
    buyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir procéder à cet achat ?')) {
                e. preventDefault();
            }
        });
    });

    // ====================================
    // 3. Smooth scroll for anchor links
    // ====================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor. addEventListener('click', function (e) {
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

    // ====================================
    // 4. Form validation enhancement
    // ====================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Chargement...';
            }
        });
    });

    console.log('✅ Knowledge Learning interactions initialized!');
});
