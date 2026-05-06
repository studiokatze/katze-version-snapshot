document.addEventListener('DOMContentLoaded', function () {
    var markdownField = document.getElementById('wvr-markdown');
    var copyNotice = document.querySelector('.js-wvr-copy-notice');

    if (!markdownField || !copyNotice) {
        return;
    }

    markdownField.addEventListener('click', function () {
        markdownField.focus();
        markdownField.select();
        markdownField.setSelectionRange(0, markdownField.value.length);

        if (!navigator.clipboard || !window.isSecureContext) {
            document.execCommand('copy');
            showCopiedMessage();
            return;
        }

        navigator.clipboard.writeText(markdownField.value).then(showCopiedMessage, function () {
            document.execCommand('copy');
            showCopiedMessage();
        });
    });

    function showCopiedMessage() {
        copyNotice.classList.add('is-visible');
        resetHideTimer(copyNotice);
    }

    function resetHideTimer(message) {
        if (message.hideTimer) {
            window.clearTimeout(message.hideTimer);
        }

        message.hideTimer = window.setTimeout(function () {
            message.classList.remove('is-visible');
        }, 1800);
    }
});
