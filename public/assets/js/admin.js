document.addEventListener('DOMContentLoaded', () => {
    // 1. Dynamic deletion handler for table items (Categories, Questions, PDFs)
    const deleteButtons = document.querySelectorAll('.btn-delete-ajax');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const deleteUrl = this.dataset.url;
            const itemId = this.dataset.id;
            const itemType = this.dataset.type || 'elemento';
            const csrfToken = this.dataset.csrf;
            const tableRow = this.closest('tr');

            if (typeof Swal === 'undefined') {
                if (confirm(`¿Estás seguro de que deseas eliminar este ${itemType}?`)) {
                    performDelete(deleteUrl, csrfToken, tableRow);
                }
                return;
            }

            Swal.fire({
                title: '¿Confirmar eliminación?',
                text: `Esta acción no se puede deshacer. Se eliminará permanentemente este ${itemType} y todos sus datos relacionados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(deleteUrl, csrfToken, tableRow);
                }
            });
        });
    });

    function performDelete(url, token, row) {
        if (typeof Loader !== 'undefined') Loader.show('Eliminando...');

        const formData = new FormData();
        formData.append('csrf_token', token);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (typeof Loader !== 'undefined') Loader.hide();

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert(data.message);
                }

                // Smoothly remove row from DOM
                if (row) {
                    row.style.transition = 'all 0.4s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-30px)';
                    setTimeout(() => {
                        row.remove();
                        // If table is empty, reload page to show empty state
                        const tbody = document.querySelector('tbody');
                        if (tbody && tbody.children.length === 0) {
                            window.location.reload();
                        }
                    }, 400);
                } else {
                    window.location.reload();
                }
            } else {
                showError(data.message || 'No se pudo eliminar el elemento.');
            }
        })
        .catch(error => {
            if (typeof Loader !== 'undefined') Loader.hide();
            console.error('Error:', error);
            showError('Ocurrió un error al procesar la solicitud.');
        });
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#4f46e5'
            });
        } else {
            alert(message);
        }
    }

    // 2. Dynamic Alternatives Builder for Question forms
    const addAltBtn = document.getElementById('btn-add-alternative');
    const builderList = document.getElementById('alternatives-builder-list');

    if (addAltBtn && builderList) {
        addAltBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            const itemCount = builderList.querySelectorAll('.builder-item').length;
            if (itemCount >= 6) {
                showError('No se recomienda agregar más de 6 alternativas por pregunta.');
                return;
            }

            const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
            const nextLetter = letters[itemCount] || '?';

            const newItem = document.createElement('div');
            newItem.className = 'builder-item form-group';
            newItem.style.opacity = '0';
            newItem.style.transform = 'translateY(10px)';
            newItem.style.transition = 'all 0.3s ease';

            newItem.innerHTML = `
                <div class="correct-radio-label">
                    <input type="radio" name="correcta" value="${itemCount}" required>
                    <span>Correcta</span>
                </div>
                <div style="flex-grow: 1; display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 800; font-size: 1.1rem; color: var(--text-muted); width: 20px;">${nextLetter}.</span>
                    <input type="text" name="alternativas[]" class="form-control" placeholder="Texto de alternativa ${nextLetter}" required>
                </div>
                <button type="button" class="btn btn-sm btn-danger btn-remove-alt" style="width: auto; height: 42px; display: flex; align-items: center;"><i class="fas fa-trash-alt"></i></button>
            `;

            builderList.appendChild(newItem);

            // Trigger animation
            setTimeout(() => {
                newItem.style.opacity = '1';
                newItem.style.transform = 'translateY(0)';
            }, 50);

            // Re-bind remove buttons
            bindRemoveButtons();
        });

        // Initial bind
        bindRemoveButtons();
    }

    function bindRemoveButtons() {
        document.querySelectorAll('.btn-remove-alt').forEach(btn => {
            btn.onclick = function(e) {
                e.preventDefault();
                const item = this.closest('.builder-item');
                const itemsList = document.querySelectorAll('.builder-item');
                
                if (itemsList.length <= 4) {
                    showError('Una pregunta debe tener al menos 4 alternativas.');
                    return;
                }

                item.style.opacity = '0';
                item.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    item.remove();
                    reindexAlternatives();
                }, 300);
            };
        });
    }

    function reindexAlternatives() {
        const items = builderList.querySelectorAll('.builder-item');
        const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        items.forEach((item, index) => {
            // Update radio value
            const radio = item.querySelector('input[type="radio"]');
            if (radio) {
                radio.value = index;
            }

            // Update letter indicator
            const letterSpan = item.querySelector('span[style*="width: 20px"]');
            if (letterSpan) {
                const nextLetter = letters[index] || '?';
                letterSpan.textContent = `${nextLetter}.`;
                
                // Update input placeholder
                const input = item.querySelector('input[type="text"]');
                if (input) {
                    input.placeholder = `Texto de alternativa ${nextLetter}`;
                }
            }
        });
    }

    // 3. Image file input preview handler
    const fileInput = document.getElementById('imagen-file-input');
    const previewContainer = document.getElementById('image-preview-container');

    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" class="img-preview" alt="Vista previa">
                        <span style="font-size: 0.85rem; color: var(--text-muted);">${file.name}</span>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = '<span style="font-size: 0.9rem; color: var(--text-muted);">Ninguna imagen seleccionada</span>';
            }
        });
    }

    // 4. Sidebar Toggle Logic
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    const sidebarLogo = document.querySelector('.sidebar-logo');
    
    if (adminSidebar) {
        // Load state from localStorage
        const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        if (isCollapsed) {
            adminSidebar.classList.add('collapsed');
        }

        const toggleSidebar = () => {
            adminSidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar_collapsed', adminSidebar.classList.contains('collapsed'));
        };

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', toggleSidebar);
        }

        if (sidebarLogo) {
            sidebarLogo.addEventListener('click', () => {
                if (adminSidebar.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });
        }
    }

    // 5. Mobile Menu Toggle Logic
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileOverlay = document.getElementById('mobile-overlay');
    
    if (mobileMenuToggle && mobileOverlay && adminSidebar) {
        mobileMenuToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = adminSidebar.classList.contains('mobile-open') ? 'hidden' : '';
        });

        mobileOverlay.addEventListener('click', () => {
            adminSidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Close sidebar when clicking a menu item on mobile
        const sidebarLinks = adminSidebar.querySelectorAll('.sidebar-item a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    adminSidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // 6. Notification Dropdown Toggle
    const notificationToggle = document.getElementById('notification-toggle');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const notificationCount = document.getElementById('notification-count');
    const notificationItems = document.querySelectorAll('.notification-item');

    console.log('Notification elements:', { notificationToggle, notificationDropdown, notificationItems });

    // Load global read notifications from localStorage
    const globalReadNotifications = JSON.parse(localStorage.getItem('global_read_notifications') || '[]');

    // Apply global read state
    notificationItems.forEach(item => {
        const notificationId = item.dataset.notificationId;
        if (globalReadNotifications.includes(notificationId)) {
            item.classList.remove('unread');
        }
    });

    // Update initial count
    updateNotificationCount();

    function markAsReadGlobal(notificationId) {
        const currentRead = JSON.parse(localStorage.getItem('global_read_notifications') || '[]');
        if (!currentRead.includes(notificationId)) {
            currentRead.push(notificationId);
            localStorage.setItem('global_read_notifications', JSON.stringify(currentRead));
        }
    }

    function updateNotificationCount() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        notificationCount.textContent = unreadCount;
        
        if (unreadCount === 0) {
            notificationCount.style.display = 'none';
        } else {
            notificationCount.style.display = 'flex';
        }
        notificationToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Toggle clicked');
            const isActive = notificationDropdown.classList.toggle('active');
            console.log('Dropdown active:', isActive);
            if (isActive) {
                notificationDropdown.classList.add('animate-fade-in');
            } else {
                notificationDropdown.classList.remove('animate-fade-in');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationToggle.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('active');
                notificationDropdown.classList.remove('animate-fade-in');
            }
        });

        // Close dropdown on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                notificationDropdown.classList.remove('active');
                notificationDropdown.classList.remove('animate-fade-in');
            }
        });
        // Mark individual notification as read (global)
        notificationItems.forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.notificationId;
                markAsReadGlobal(notificationId);
                item.classList.remove('unread');
                updateNotificationCount();
            });
        });

        // Mark all as read (global)
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                notificationItems.forEach(item => {
                    const notificationId = item.dataset.notificationId;
                    markAsReadGlobal(notificationId);
                    item.classList.remove('unread');
                });
                updateNotificationCount();
            });
        }
    }
});
