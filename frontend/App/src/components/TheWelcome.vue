<script setup lang="ts">
import WelcomeItem from './WelcomeItem.vue'
import DocumentationIcon from './icons/IconDocumentation.vue'
import ToolingIcon from './icons/IconTooling.vue'
import EcosystemIcon from './icons/IconEcosystem.vue'
import CommunityIcon from './icons/IconCommunity.vue'
import SupportIcon from './icons/IconSupport.vue'

const openReadmeInEditor = () => fetch('/__open-in-editor?file=README.md')
</script>

<template>
  <WelcomeItem>
    <template #icon>
      <DocumentationIcon />
    </template>
    <template #heading>Climate control project</template>

    <a
      href="https://github.com/SchelkovyyK/Climate-control-Iot_Project"
      target="_blank"
      rel="noopener"
      >Climate-control-Iot_Project</a
    >
  </WelcomeItem>

  <WelcomeItem>
    <template #icon>
      <ToolingIcon />
    </template>
    <template #heading>DocuSynth</template>

    <a href="https://github.com/SchelkovyyK/DocuSynth" target="_blank" rel="noopener">DocuSynth</a>
  </WelcomeItem>

  <WelcomeItem>
    <template #icon>
      <EcosystemIcon />
    </template>
    <template #heading>Fake-Detect-Project</template>

    <a href="https://github.com/SchelkovyyK/Fake-Detect-Project" target="_blank" rel="noopener"
      >Fake-Detect-Project</a
    >
  </WelcomeItem>
</template>
