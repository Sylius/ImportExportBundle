function syliusBulkExport(form) {
    const groupName = form.getAttribute('data-bulk-export');
    const groupItems = Array.from(document.querySelectorAll(`input[data-check-all-group="${groupName}"]`));

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const idsInput = form.querySelector('input[name$="[ids]"]');
        const ids = [];

        groupItems.forEach((item) => {
            if (item.checked) {
                ids.push(item.value);
            }
        });
        idsInput.setAttribute('value', ids.join(','));

        e.target.submit();
    });
}

function passDisabledAttributeToDropdownTrigger(trigger) {
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                const button = mutation.target;

                if (button.hasAttribute('disabled')) {
                    button.classList.add('disabled');
                } else {
                    button.classList.remove('disabled');
                }
            }
        }
    });

    const groupName = trigger.getAttribute('data-check-all');
    const dropdownActionButtons = Array.from(document.querySelectorAll(`.dropdown-toggle[data-check-all-action="${groupName}"]`));

    dropdownActionButtons.forEach(button => {
        observer.observe(button, {'attributes': true});
    })
}

(function () {
    document.querySelectorAll('[data-check-all]').forEach(passDisabledAttributeToDropdownTrigger);
    document.querySelectorAll('[data-bulk-export]').forEach(syliusBulkExport);
}());
