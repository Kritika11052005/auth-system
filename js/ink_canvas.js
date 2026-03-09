/**
 * Organic Ink - Interactive Canvas
 * Creates subtle, generative ink bleeds that react to mouse movement.
 */

class InkCanvas {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.maxParticles = 40;
        this.mouse = { x: -100, y: -100 };
        
        this.init();
    }

    init() {
        this.canvas.style.position = 'fixed';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        this.canvas.style.zIndex = '-1';
        this.canvas.style.pointerEvents = 'none';
        this.canvas.style.opacity = '0.35';
        
        document.body.prepend(this.canvas);
        this.resize();

        window.addEventListener('resize', () => this.resize());
        window.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
            if (Math.random() > 0.8) this.createParticle(this.mouse.x, this.mouse.y);
        });

        this.animate();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    createParticle(x, y) {
        if (this.particles.length > this.maxParticles) this.particles.shift();
        
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const inkColor = isDark ? 'rgba(122, 112, 96, 0.15)' : 'rgba(45, 90, 61, 0.08)';

        this.particles.push({
            x, y,
            radius: Math.random() * 60 + 20,
            grow: Math.random() * 0.5 + 0.2,
            opacity: 1,
            color: inkColor
        });
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Subtle noise texture overlay (simulated)
        this.particles.forEach((p, i) => {
            p.radius += p.grow;
            p.opacity -= 0.005;

            if (p.opacity <= 0) {
                this.particles.splice(i, 1);
                return;
            }

            this.ctx.beginPath();
            const gradient = this.ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.radius);
            const colorParts = p.color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            if (colorParts) {
                const [_, r, g, b] = colorParts;
                gradient.addColorStop(0, `rgba(${r},${g},${b},${p.opacity * 0.5})`);
                gradient.addColorStop(1, `rgba(${r},${g},${b},0)`);
            }
            
            this.ctx.fillStyle = gradient;
            this.ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            this.ctx.fill();
        });

        requestAnimationFrame(() => this.animate());
    }
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    window.InkBackground = new InkCanvas();
});
