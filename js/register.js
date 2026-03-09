/**
 * Registration Logic
 */

$(document).ready(function() {
    const passwordInput = $('#password');
    const strengthBar = $('#strength-bar');
    const strengthLabel = $('#strength-label');

    // Password Toggle Logic
    $('.password-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const input = $(`#${targetId}`);
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        $(this).text(type === 'password' ? '👁' : '🙈');
    });

    // Password Strength Meter
    passwordInput.on('input', function() {
        const password = $(this).val();
        const strength = Utils.checkPasswordStrength(password);
        
        const colors = ['#B84A2A', '#C4622D', '#D4A43A', '#81A1C1', '#3A7D52'];
        const labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
        
        const percentage = (strength / 5) * 100;
        strengthBar.css({
            'width': `${percentage}%`,
            'background-color': colors[Math.max(0, strength - 1)]
        });
        
        strengthLabel.text(labels[Math.max(0, strength - 1)]);
    });

    // Form Submission
    $('#register-form').on('submit', function(e) {
        e.preventDefault();

        // Validation
        const password = $('#password').val();
        const confirm = $('#confirm_password').val();

        if (password !== confirm) {
            Utils.showToast("Passwords do not match", "error");
            $('#confirm_password').addClass('error').addClass('shake');
            setTimeout(() => $('#confirm_password').removeClass('shake'), 400);
            return;
        }

        const formData = $(this).serialize();
        Utils.setLoading('register-btn', true);

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Utils.setLoading('register-btn', false);
                if (response.success) {
                    Utils.showToast(response.message, "success");
                    // Redirect to login after delay
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    Utils.showToast(response.message, "error");
                }
            },
            error: function() {
                Utils.setLoading('register-btn', false);
                Utils.showToast("An unexpected error occurred", "error");
            }
        });
    });
});
