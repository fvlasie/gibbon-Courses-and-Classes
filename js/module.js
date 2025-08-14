window.onbeforeunload = null;

function isModalVisible() {
  const modal = document.getElementById('modal');
  return modal && getComputedStyle(modal).display !== 'none';
}

let previousVisible = null;

const checkModalVisibility = () => {
  const currentlyVisible = isModalVisible();

  if (previousVisible === true && currentlyVisible === false) {
    // Modal just closed
    location.reload();
  }

  previousVisible = currentlyVisible;
};

// Run every 100ms (or tweak as needed)
setInterval(checkModalVisibility, 500);


document.body.addEventListener('htmx:afterRequest', function (e) {
  const form = e.target.closest('form');
  if (form && form.id === 'uploadForm' && e.detail.successful) {
    form.reset();
    window.onbeforeunload = null;
  }
});

