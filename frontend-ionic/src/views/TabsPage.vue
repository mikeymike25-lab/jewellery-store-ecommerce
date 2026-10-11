<script setup lang="ts">
import {
  IonTabs,
  IonPage,
  IonRouterOutlet,
} from '@ionic/vue';
</script>

<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet :animated="false" />
    </ion-tabs>
  </ion-page>
</template>
