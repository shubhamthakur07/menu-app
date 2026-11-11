/**
 * Universal Sound & Notification Helper for QRMenu (Web Audio API + Notification API)
 * Zero external audio files required — synthesized in real-time by browser audio engine.
 */

// Synthesize pleasant multi-tone chimes using Web Audio API
export const playSound = (type = 'chime') => {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();
    const now = ctx.currentTime;

    if (type === 'new_order') {
      // Cheerful Two-Tone Kitchen Bell (880Hz -> 1320Hz)
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, now);
      osc.frequency.setValueAtTime(1320, now + 0.14);

      gain.gain.setValueAtTime(0.35, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);

      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.6);

    } else if (type === 'service_bell') {
      // Distinct High-Pitch Service Bell Ping (1760Hz - A6)
      [0, 0.18, 0.36].forEach((delay) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1760, now + delay);
        gain.gain.setValueAtTime(0.3, now + delay);
        gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.4);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + delay);
        osc.stop(now + delay + 0.4);
      });

    } else if (type === 'payment_success') {
      // Celebratory C-Major 4-Chord Arpeggio (C5 - E5 - G5 - C6)
      const freqs = [523.25, 659.25, 783.99, 1046.50];
      freqs.forEach((freq, idx) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        const start = now + idx * 0.08;

        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, start);

        gain.gain.setValueAtTime(0.25, start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.45);

        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(start);
        osc.stop(start + 0.45);
      });

    } else if (type === 'status_update') {
      // Gentle status chime (659Hz -> 880Hz)
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(659.25, now);
      osc.frequency.setValueAtTime(880, now + 0.12);

      gain.gain.setValueAtTime(0.28, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);

      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.5);
    }
  } catch (err) {
    // Audio context may be restricted before first user gesture
  }

  // Trigger device haptic vibration on Android/mobile if supported
  try {
    if (navigator.vibrate) {
      if (type === 'new_order') navigator.vibrate([150, 75, 150]);
      else if (type === 'payment_success') navigator.vibrate([100, 50, 100, 50, 150]);
      else navigator.vibrate(100);
    }
  } catch (e) {}
};

// Request System Notification Permission
export const requestNotificationPermission = async () => {
  try {
    if ('Notification' in window && Notification.permission === 'default') {
      return await Notification.requestPermission();
    }
    return Notification.permission;
  } catch (e) {
    return 'denied';
  }
};

// Trigger system/browser notification if permission granted
export const sendSystemNotification = (title, body, icon = '/icon-192.png') => {
  try {
    if ('Notification' in window && Notification.permission === 'granted') {
      new Notification(title, {
        body,
        icon,
        badge: '/icon-192.png',
      });
    }
  } catch (e) {}
};

// Fun and lightweight festive confetti burst
export const launchConfetti = () => {
  try {
    const container = document.createElement('div');
    container.style.position = 'fixed';
    container.style.inset = '0';
    container.style.pointerEvents = 'none';
    container.style.zIndex = '99999';
    container.style.overflow = 'hidden';
    document.body.appendChild(container);

    const colors = ['#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#3b82f6', '#ef4444', '#ffd700'];
    const count = 50;

    for (let i = 0; i < count; i++) {
      const confetti = document.createElement('div');
      const size = Math.random() * 8 + 6;
      confetti.style.position = 'absolute';
      confetti.style.width = `${size}px`;
      confetti.style.height = `${size * (Math.random() > 0.5 ? 1.5 : 1)}px`;
      confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
      confetti.style.left = `${Math.random() * 100}vw`;
      confetti.style.top = '-20px';
      confetti.style.opacity = '1';
      confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
      confetti.style.transition = `all ${Math.random() * 2 + 1.5}s cubic-bezier(0.25, 0.46, 0.45, 0.94)`;
      container.appendChild(confetti);

      setTimeout(() => {
        confetti.style.top = `${window.innerHeight + 50}px`;
        confetti.style.left = `${parseFloat(confetti.style.left) + (Math.random() * 20 - 10)}vw`;
        confetti.style.transform = `rotate(${Math.random() * 720}deg)`;
        confetti.style.opacity = '0';
      }, 50);
    }

    setTimeout(() => {
      container.remove();
    }, 3500);
  } catch (e) {}
};
