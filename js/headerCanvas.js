(function () {
var particlesOptions = {
        "particles": {
            "number": {
                "value": 110,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "img/github.svg",
                    "width": 100,
                    "height": 100
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 1,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 20,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 40,
                "color": "#fff",
                "opacity": 1,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 1,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 140,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true,
        "config_demo": {
            "hide_card": false,
            "background_color": "#b61924",
            "background_image": "",
            "background_position": "50% 50%",
            "background_repeat": "no-repeat",
            "background_size": "cover"
        }
    };

var _particlesInitialized = false;
var _fallbackAnimationId = null;

function initParticles() {
    if (_particlesInitialized) return;

    var canvas = document.getElementById('header-canvas');
    if (!canvas) return;

    function getCanvasSize() {
        var rect = canvas.getBoundingClientRect();
        var width = Math.round(rect.width || canvas.offsetWidth || 0);
        var height = Math.round(rect.height || canvas.offsetHeight || 0);

        if ((width === 0 || height === 0) && canvas.parentElement) {
            var pRect = canvas.parentElement.getBoundingClientRect();
            width = width || Math.round(pRect.width || 0);
            height = height || Math.round(pRect.height || 0);
        }

        return {
            width: width,
            height: height
        };
    }

    function applyCanvasSize(size) {
        canvas.width = Math.max(size.width, 1);
        canvas.height = Math.max(size.height, 1);
        canvas.style.width = '100%';
        canvas.style.height = '100%';
    }

    var size = getCanvasSize();
    if (size.width === 0 || size.height === 0) {
        applyCanvasSize({ width: window.innerWidth || 1280, height: 200 });
    } else {
        applyCanvasSize(size);
    }

    if (typeof particlesJS !== 'undefined') {
        try {
            particlesJS('header-canvas', particlesOptions);
            _particlesInitialized = true;
            return;
        } catch(e) { /* fall through */ }
    }

    // Fallback: simple canvas particle animation
    try {
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var particles = [];
        for (var i = 0; i < 50; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 2,
                vy: (Math.random() - 0.5) * 2,
                radius: Math.random() * 2 + 1
            });
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
            particles.forEach(function(p) {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fill();
            });
            _fallbackAnimationId = requestAnimationFrame(animate);
        }

        animate();
        _particlesInitialized = true;

        window.addEventListener('resize', function() {
            if (!_particlesInitialized) return;
            var newSize = getCanvasSize();
            if (newSize.width > 0 && newSize.height > 0) {
                applyCanvasSize(newSize);
            }
        });
    } catch (e) { /* silently fail */ }
}

function initParticlesWhenReady() {
    var maxRetries = 24;

    function attempt() {
        var canvas = document.getElementById('header-canvas');
        if (!canvas) return;

        var rect = canvas.getBoundingClientRect();
        if ((rect.width > 0 && rect.height > 0) || maxRetries <= 0) {
            initParticles();
            return;
        }

        maxRetries -= 1;
        requestAnimationFrame(attempt);
    }

    attempt();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initParticlesWhenReady);
} else {
    initParticlesWhenReady();
}
window.addEventListener('load', initParticlesWhenReady);
})();
