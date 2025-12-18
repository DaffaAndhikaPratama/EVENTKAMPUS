document.addEventListener("DOMContentLoaded", function() {
    const photoInput = document.getElementById('photoInput');
    const profilePreview = document.getElementById('profilePreview');

    if (photoInput && profilePreview) {
        
        photoInput.addEventListener('change', function(e) {
            const input = e.target;
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        });
    }
});