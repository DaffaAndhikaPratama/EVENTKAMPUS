function goBack(fallbackUrl) {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = fallbackUrl;
    }
}