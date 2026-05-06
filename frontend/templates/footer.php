<?php
/**
 * Template: Footer - Apotek Ananda Jadimulya
 */
?>
    </main> <!-- end main-content -->
</div> <!-- end wrapper -->

<script>
/**
 * Global functions for Apotek Ananda
 */
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('active');
}

// Global confirm for delete actions
document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin melakukan tindakan ini?')) {
            e.preventDefault();
        }
    });
});

// Autocomplete Logic
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.autocomplete-input');
    
    inputs.forEach(input => {
        // Wrap input and create suggestions box
        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-container';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const suggestionsBox = document.createElement('div');
        suggestionsBox.className = 'autocomplete-suggestions';
        wrapper.appendChild(suggestionsBox);
        
        let debounceTimer;
        
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const val = this.value;
            const type = this.getAttribute('data-type');
            
            if (!val || val.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`<?= BASE_URL ?>/backend/controllers/api_search.php?type=${type}&term=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.innerHTML = `<span class="material-icons-round">history</span>${item.label}`;
                                div.addEventListener('click', function() {
                                    input.value = item.value;
                                    suggestionsBox.style.display = 'none';
                                    input.form.submit(); // Auto submit
                                });
                                suggestionsBox.appendChild(div);
                            });
                            suggestionsBox.style.display = 'block';
                        } else {
                            suggestionsBox.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Autocomplete error:', err);
                        suggestionsBox.style.display = 'none';
                    });
            }, 300);
        });
        
        // Hide on click outside
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
    });
});
</script>
</body>
</html>
