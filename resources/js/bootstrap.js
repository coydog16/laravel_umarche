import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import MicroModal from 'micromodal';  // es6 module
MicroModal.init({
  disableScroll: true
});