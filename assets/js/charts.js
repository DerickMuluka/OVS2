// Loads vote results via AJAX and renders bar + pie charts
async function loadCharts(electionId) {
    try {
        const res  = await fetch(`../api/get_results.php?election_id=${electionId}`);
        const data = await res.json();

        const labels = data.map(d => d.candidate_name);
        const votes  = data.map(d => Number(d.total_votes));
        const colors = ['#7c3aed','#f59e0b','#10b981','#ef4444','#8b5cf6','#ec4899'];

        // Bar chart
        const barCtx = document.getElementById('barChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Votes',
                        data: votes,
                        backgroundColor: colors,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // Pie chart
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: votes,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    } catch (err) {
        console.error('Chart load failed:', err);
    }
}