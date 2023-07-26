<template>

  <div class="text-sm">
    <div v-if="meta.missing_file" class="text-red-500">
      Tailwind config file not found. Did you run <code>php artisan kit:tailwind-config</code>?
    </div>
    <div v-else class="flex items-center mb-2" v-for="color in config.available_colors" :key="color">
      <div class="w-24 text-right px-4">{{ color }}</div>
      <div v-if="mode === 'advanced'">
        <div v-if="(typeof colors[color] == 'string')">
          <swatch @update="setColor(color)" :color="{color: color, weight: null, hex: colors[color]}" :active="selected" />
        </div>
        <div v-else class="flex">
          <swatch @update="setColor(color, weight)" v-for="(hex, weight) in colors[color]" :key="hex + weight" :color="{color, weight, hex}" :active="selected" />
        </div>
      </div>
      <div v-else>
        <div>
          <swatch v-if="colors[color][config.default_color] || color === 'transparent'" @update="setColor(color, default_color)" :color="{color: color, weight: config.default_color, hex: colors[color][config.default_color]}" :active="selected" />
        </div>
      </div>
    </div>
  </div>

</template>

<script>
import Swatch from './Swatch.vue';
export default {

  mixins: [Fieldtype],

  props: {
    value: {
      type: String,
      default: '',
    },
    config: {
      type: Object,
      default() {
        return {}
      }
    }
  },

  data() {
    return {
      selected: {
        color: null,
        weight: null,
      }
    };
  },
  mounted() {
    if (this.value) {
      let parts = this.value.split('-');
      if (parts.length == 2) {
        this.selected.color = parts[0];
        this.selected.weight = parts[1];
      }

      if (parts.length == 3) {
        this.selected.color = parts[1];
        this.selected.weight = parts[2];
      }
    }

  },
  computed: {
    colors() {
      return this.meta.colors;
    },
    mode() {
      return this.config.mode;
    },
  },
  methods: {
    setColor(color, weight = null) {
      console.log(color, weight);
      this.selected.color = color;
      this.selected.weight = weight;

      let className = '';
      if (this.config.class_prefix) {
        className = this.config.class_prefix + '-';
      }
      className += color;

      if (this.config.mode === 'simple') {
        weight = this.config.default_color;
      }

      if (weight) {
        className += '-' + weight;
      }

      console.log(className);

      this.$emit('input', className);
    }
  },
  components: {
    Swatch,
  }

};
</script>

