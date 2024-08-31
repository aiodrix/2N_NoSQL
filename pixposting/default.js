        function toggleDescription(event) {
            event.preventDefault();
            const description = event.target.nextElementSibling;
            description.classList.toggle('show');
        }