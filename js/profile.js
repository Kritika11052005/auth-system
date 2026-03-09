/**
 * Profile Logic - Organic Ink
 * Handles data fetching, form submission, and dynamic UI updates
 */

$(document).ready(function() {
    // Check if logged in
    if (!Utils.isLoggedIn()) {
        window.location.href = 'login.html';
        return;
    }

    // Load Profile Data
    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${Utils.getToken()}`
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const account = data.account;
                    const profile = data.profile || {};

                    // 1. Update Header
                    $('#display-name').text(`Welcome back, ${account.name.split(' ')[0]}.`);
                    $('#display-username').text(`@${account.username}`);
                    updateAvatar(account.name);

                    // 2. Update Form Fields
                    $('#name').val(account.name);
                    $('#email').val(account.email); // This fixes the hardcoded/placeholder issue
                    
                    // Signature Loading
                    if (profile.signature) {
                        const img = new Image();
                        img.onload = function() {
                            const sigPad = document.getElementById('signature-pad');
                            if (sigPad) sigPad.getContext('2d').drawImage(img, 0, 0);
                        };
                        img.src = profile.signature;
                        $('#signature-data').val(profile.signature);
                    }
                    
                    // Profile details from MongoDB
                    $('#contact').val(profile.contact || '');
                    $('#age').val(profile.age || '');
                    
                    if (profile.dob) {
                        $('#dob').val(profile.dob);
                        // Update flatpickr instance if it exists
                        const fp = document.querySelector("#dob")._flatpickr;
                        if (fp) fp.setDate(profile.dob);
                    }

                    $('#gender').val(profile.gender || '');
                    $('#city').val(profile.city || '');
                    $('#country').val(profile.country || '');
                    $('#bio').val(profile.bio || '');

                    // Update last updated text if available
                    if (profile.updated_at) {
                        $('#last-updated').text(new Date(profile.updated_at).toLocaleString());
                    }
                } else {
                    Utils.showToast("Failed to load profile", "error");
                    if (response.message && response.message.includes("Unauthorized")) {
                        Utils.logout();
                    }
                }
            },
            error: function() {
                Utils.showToast("Network error occurred", "error");
            }
        });
    }

    // Deterministic Avatar Generation
    function updateAvatar(name) {
        if (!name) return;
        const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        $('#user-avatar').text(initials);

        // Organic Gradient Pairs
        const gradients = [
            'linear-gradient(135deg, #2D5A3D 0%, #7BAF8A 100%)',
            'linear-gradient(135deg, #C4622D 0%, #D4A43A 100%)',
            'linear-gradient(135deg, #1C1A16 0%, #7A7060 100%)',
            'linear-gradient(135deg, #3A7D52 0%, #1E3D2A 100%)',
            'linear-gradient(135deg, #B84A2A 0%, #C4622D 100%)',
            'linear-gradient(135deg, #7A7060 0%, #D6CEB8 100%)'
        ];

        // Hash name to pick gradient
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        const index = Math.abs(hash % gradients.length);
        $('#user-avatar').css('background', gradients[index]);
    }

    // Handle form submission
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        Utils.setLoading('save-btn', true);

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            headers: {
                'Authorization': `Bearer ${Utils.getToken()}`
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                Utils.setLoading('save-btn', false);
                if (response.success) {
                    Utils.showToast("Profile updated successfully", "success");
                    $('#save-btn').addClass('btn-saved').html('✦ Saved');
                    setTimeout(() => {
                        $('#save-btn').removeClass('btn-saved').html('Save Changes ✦');
                    }, 2000);
                    
                    // Refresh avatar and header name if changed
                    const newName = $('#name').val();
                    updateAvatar(newName);
                    $('#display-name').text(`Welcome back, ${newName.split(' ')[0]}.`);
                } else {
                    Utils.showToast(response.message, "error");
                }
            },
            error: function() {
                Utils.setLoading('save-btn', false);
                Utils.showToast("An unexpected error occurred", "error");
            }
        });
    });

    // Logout
    $('#logout-btn').on('click', Utils.logout);

    // Initial load
    loadProfile();

    // ── Signature Pad Logic ──────────────────────────────────────────
    const sigCanvas = document.getElementById('signature-pad');
    if (sigCanvas) {
        const ctx = sigCanvas.getContext('2d');
        let painting = false;

        function resizeSig() {
            const rect = sigCanvas.getBoundingClientRect();
            sigCanvas.width = rect.width;
            sigCanvas.height = rect.height;
        }
        
        resizeSig();
        $(window).on('resize', resizeSig);

        function startPosition(e) {
            painting = true;
            draw(e);
        }

        function finishedPosition() {
            painting = false;
            ctx.beginPath();
            // Store as base64
            $('#signature-data').val(sigCanvas.toDataURL());
        }

        function draw(e) {
            if (!painting) return;
            const rect = sigCanvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;

            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--primary-accent');

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        sigCanvas.addEventListener('mousedown', startPosition);
        sigCanvas.addEventListener('touchstart', startPosition);
        window.addEventListener('mouseup', finishedPosition);
        window.addEventListener('touchend', finishedPosition);
        sigCanvas.addEventListener('mousemove', draw);
        sigCanvas.addEventListener('touchmove', draw);

        $('#clear-signature').on('click', () => {
            ctx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
            $('#signature-data').val('');
        });
    }

    // Initialize Beautiful Calendar (Flatpickr)
    flatpickr("#dob", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        animate: true,
        disableMobile: "true"
    });
});

