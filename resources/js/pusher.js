import Pusher from 'pusher-js';

Pusher.logToConsole = true;

const pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  encrypted: true,
  wsHost: process.env.MIX_PUSHER_HOST || '127.0.0.1',
  wsPort: process.env.MIX_PUSHER_PORT || 6001,
  forceTLS: process.env.MIX_PUSHER_SCHEME === 'https',
  disableStats: true
});

export default pusher;
