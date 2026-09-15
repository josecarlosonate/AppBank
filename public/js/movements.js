document.addEventListener("DOMContentLoaded", async () => {
  const movementsChart = document.getElementById("movementsChart");
  if (!movementsChart) return;

  const accountId = movementsChart.dataset.accountId;
  if (!accountId) return;

  try {
    const response = await fetch(`/movements/summary?account_id=${accountId}`);

    if (!response.ok) {
      console.error("No se pudo cargar el resumen");
      return;
    }

    const monthlySummary = await response.json();

    const labels = monthlySummary.map((item) => {
      const [year, month] = item.month.split("-");
      const date = new Date(Number(year), Number(month));

      const monthName = new Intl.DateTimeFormat("es-CO", {
        month: "long",
      }).format(date);

      return `${monthName} - ${year}`;
    });

    const debits = monthlySummary.map((item) => Number(item.total_debit));
    const credits = monthlySummary.map((item) => Number(item.total_credit));

    new Chart(movementsChart, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            label: "Gastos",
            data: debits,
            borderWidth: 1,
            backgroundColor: "rgba(110, 168, 254, 0.85)",
          },
          {
            label: "Ingresos",
            data: credits,
            borderWidth: 1,
            backgroundColor: "rgba(248, 164, 180, 0.85)",
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: "top" },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "$" + value.toLocaleString("es-CO");
              },
            },
          },
        },
      },
    });
  } catch (error) {
    console.error(error);
  }
});
