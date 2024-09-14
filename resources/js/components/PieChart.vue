<template>
    <div>
      <Pie :data="chartData" :options="chartOptions" />
      <div class="legend">
        <div v-for="(value, label) in legendData" :key="label" class="legend-item">
          <span class="legend-color" :style="{ backgroundColor: value.color }"></span>
          <span>{{ label }}: {{ value.count }}</span>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { Pie } from 'vue-chartjs';
  import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';
  
  ChartJS.register(Title, Tooltip, Legend, ArcElement);
  
  export default {
    name: 'PieChart',
    components: {
      Pie
    },
    props: {
      chartData: {
        type: Object,
        required: true
      },
      chartOptions: {
        type: Object,
        default: () => ({
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: (context) => `Contagem: ${context.raw}`,
              },
            },
          },
        }),
      },
      legendData: {
        type: Object,
        required: true
      }
    }
  };
  </script>
  
  <style scoped>
  .legend {
    margin-top: 1rem;
  }
  .legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
  }
  .legend-color {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    border-radius: 50%;
  }
  </style>
  