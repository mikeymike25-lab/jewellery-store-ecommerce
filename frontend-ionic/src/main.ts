import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { IonicVue } from '@ionic/vue';
import router from './router';
import App from './App.vue';

import { vScrollReveal } from './directives/scroll-reveal';
import './style.css';

const app = createApp(App);
const pinia = createPinia();

app.directive('scroll-reveal', vScrollReveal);

app.use(IonicVue, {
  animated: false,
});
app.use(pinia);
app.use(router);

router.isReady().then(() => {
  app.mount('#app');
});
