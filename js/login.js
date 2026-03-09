/**
 * Login Logic
 */

$(document).ready(function() {
    // Password visibility toggle
    $('#password-toggle').on('click', function() {
        const input = $('#password');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).text(type === 'password' ? '👁' : '🙈');
    });

    // Form Submission
    $('#login-form').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            identifier: $('#identifier').val(),
            password: $('#password').val(),
            remember: $('#remember').is(':checked')
        };

        Utils.setLoading('login-btn', true);

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Utils.setLoading('login-btn', false);
                if (response.success) {
                    Utils.showToast(response.message, "success");
                    Utils.setToken(response.token);
                    
                    // Small delay for toast visibility
                    setTimeout(() => {
                        window.location.href = 'profile.html';
                    }, 1500);
                } else {
                    Utils.showToast(response.message, "error");
                    $('.login-card').addClass('shake');
                    setTimeout(() => $('.login-card').removeClass('shake'), 400);
                }
            },
            error: function() {
                Utils.setLoading('login-btn', false);
                Utils.showToast("An unexpected error occurred", "error");
            }
        });
    });
});
