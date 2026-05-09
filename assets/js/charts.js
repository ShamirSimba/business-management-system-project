// Charts JavaScript file for BMS

let salesChartInstance = null;
let profitChartInstance = null;

function initSalesChart(labels, data) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;
    if (salesChartInstance) {
        salesChartInstance.destroy();
    }

    salesChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Sales',
                data,
                backgroundColor: 'rgba(39, 174, 96, 0.8)',
                borderRadius: 12,
                maxBarThickness: 36
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#334155' }
                },
                y: {
                    grid: { color: 'rgba(15, 23, 42, 0.08)' },
                    ticks: { color: '#334155' }
                }
            }
        }
    });
}

function initProfitChart(labels, profitData, expenseData) {
    const ctx = document.getElementById('profitChart');
    if (!ctx) return;
    if (profitChartInstance) {
        profitChartInstance.destroy();
    }

    profitChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Net Profit',
                    data: profitData,
                    borderColor: '#1e3a5f',
                    backgroundColor: 'rgba(30, 58, 95, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                },
                {
                    label: 'Expenses',
                    data: expenseData,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#334155' }
                },
                y: {
                    grid: { color: 'rgba(15, 23, 42, 0.08)' },
                    ticks: { color: '#334155' }
                }
            }
        }
    });
}
