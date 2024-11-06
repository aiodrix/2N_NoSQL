document.addEventListener('DOMContentLoaded', function() {
    const adsBanner = document.querySelector('.ads');
    adsBanner.innerHTML = '<p><i><a href="#" style="color: #999; text-decoration: none;">Top</a></p>';
});

/*/
document.addEventListener('scroll', function() {
    const adsBanner = document.querySelector('.ads');
    const scrollHeight = document.documentElement.scrollHeight;
    const scrollTop = document.documentElement.scrollTop;
    const clientHeight = document.documentElement.clientHeight;

    if (scrollTop + clientHeight >= scrollHeight) {
        if (adsBanner.innerHTML.trim() === '') {
            adsBanner.innerHTML = '<p><i>follow on X (Twitter) <a href="https://twitter.com/2_nodes" target="_blank">@2_nodes</a> </i></p>'; // Add the content dynamically
        }
    }
});
/*/