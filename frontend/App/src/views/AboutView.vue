<template>
  <div class="about">
    <swiper
      :style="{
        '--swiper-navigation-color': '#fff',
        '--swiper-pagination-color': '#fff',
      }"
      :loop="true"
      :spaceBetween="10"
      :navigation="true"
      :thumbs="{ swiper: thumbsSwiper }"
      :modules="modules"
      class="mySwiper2"
    >
      <swiper-slide
        ><img src="../../public/images/dfb175aafbd34ff2d5c5306d8421c69a.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/704cac241c6ab84d1fce9e4a76e00226.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/924192b2cdbec6802e7fe4229e2e1bd9.jpg"
      /></swiper-slide>

      <swiper-slide
        ><img src="../../public/images/7c671e59434500a7bbce783166ea31e5.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/b42e614551ef0cd1d8f9e67914cd8d64.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/87051939c904c85d4244237993cedb13.jpg"
      /></swiper-slide>
    </swiper>

    <swiper
      @swiper="setThumbsSwiper"
      :loop="true"
      :spaceBetween="10"
      :slidesPerView="3"
      :freeMode="true"
      :watchSlidesProgress="true"
      :modules="modules"
      class="mySwiper"
    >
      <swiper-slide
        ><img src="../../public/images/dfb175aafbd34ff2d5c5306d8421c69a.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/704cac241c6ab84d1fce9e4a76e00226.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/924192b2cdbec6802e7fe4229e2e1bd9.jpg"
      /></swiper-slide>

      <swiper-slide
        ><img src="../../public/images/7c671e59434500a7bbce783166ea31e5.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/b42e614551ef0cd1d8f9e67914cd8d64.jpg"
      /></swiper-slide>
      <swiper-slide
        ><img src="../../public/images/87051939c904c85d4244237993cedb13.jpg"
      /></swiper-slide>
    </swiper>
  </div>
</template>
<script>
import { ref } from 'vue'
// Import Swiper Vue.js components
import { Swiper, SwiperSlide } from 'swiper/vue'

// Import Swiper styles
import 'swiper/css'

import 'swiper/css/free-mode'
import 'swiper/css/navigation'
import 'swiper/css/thumbs'

import '../assets/main.css'

// import required modules
import { FreeMode, Navigation, Thumbs } from 'swiper/modules'

export default {
  components: {
    Swiper,
    SwiperSlide,
  },
  setup() {
    const thumbsSwiper = ref(null)

    const setThumbsSwiper = (swiper) => {
      thumbsSwiper.value = swiper
    }

    return {
      thumbsSwiper,
      setThumbsSwiper,
      modules: [FreeMode, Navigation, Thumbs],
    }
  },
}
</script>

<style>
.about {
  width: 50vw;
  height: 90vh;

  display: flex;
  flex-direction: column;
}

@media (min-width: 1024px) {
  .about {
    min-height: 10vh;
    align-items: center;
  }
}
</style>
