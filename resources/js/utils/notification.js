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
