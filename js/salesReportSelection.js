// salesreportselection.js

function handleReportTypeChange() {
    const reportTypeSelect = document.getElementById('reportType');
    const selectedReportType = reportTypeSelect.value;

    // Hide all report forms initially
    const reportForms = document.querySelectorAll('.reportForm');
    reportForms.forEach(form => {
        form.style.display = 'none';
    });

    // Show the selected report form
    const selectedForm = document.querySelector(`.reportForm[data-report-type="${selectedReportType}"]`);
    if (selectedForm) {
        selectedForm.style.display = 'block';
    }
}

// Initialize the script on page load
document.addEventListener('DOMContentLoaded', () => {
    handleReportTypeChange(); // Call to set the initial state based on the selected type
});
