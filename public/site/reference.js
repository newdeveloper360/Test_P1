(() => {
    const target = document.querySelector('[data-typing]');
    if (!target || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const phrases = ['Stay connected.', 'See your day.', 'Keep your history.'];
    let phrase = 0, position = phrases[0].length, deleting = true, timer;
    function tick() {
        if (document.hidden) return;
        const text = phrases[phrase];
        position += deleting ? -1 : 1;
        target.textContent = text.slice(0, position);
        let delay = deleting ? 45 : 85;
        if (position === 0) { deleting = false; phrase = (phrase + 1) % phrases.length; delay = 350; }
        else if (!deleting && position === text.length) { deleting = true; delay = 2400; }
        timer = window.setTimeout(tick, delay);
    }
    timer = window.setTimeout(tick, 2800);
    document.addEventListener('visibilitychange', () => {
        window.clearTimeout(timer);
        if (!document.hidden) timer = window.setTimeout(tick, 500);
    });
})();
