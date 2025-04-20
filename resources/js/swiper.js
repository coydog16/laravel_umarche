import Swiper from 'swiper';
import { Navigation, Pagination, Scrollbar } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/scrollbar';

// DOMContentLoadedイベントでの初期化
window.addEventListener('DOMContentLoaded', function() {
  // すでにSwiperが初期化されているか確認
  if (document.querySelector('.swiper')) {
    const swiper = new Swiper('.swiper', {

      modules: [Navigation, Pagination, Scrollbar],

      loop: true,
      pagination: {
        el: '.swiper-pagination',
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      scrollbar: {
        el: '.swiper-scrollbar',
      },
    });
    
    console.log('Swiper initialized:', swiper);
  }
});