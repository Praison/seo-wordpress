// Logging template to add to all AJAX calls
const loggingTemplate = `
        // 🔴 COMPREHENSIVE DEBUG LOGGING
        console.log('========================================');
        console.log('🔴 AJAX REQUEST STARTING');
        console.log('========================================');
        console.log('Timestamp:', new Date().toISOString());
        console.log('Action:', action);
        console.log('AJAX URL:', ajaxurl);
        console.log('---');
        console.log('aiseoAdmin object:', aiseoAdmin);
        console.log('aiseoAdmin.nonce:', aiseoAdmin ? aiseoAdmin.nonce : 'UNDEFINED');
        console.log('Nonce being sent:', aiseoAdmin.nonce);
        console.log('Request data:', data);
        console.log('========================================');
`;

console.log('Add this logging before each $.ajax() call in:');
console.log('- bulk-operations.php');
console.log('- ai-content.php');
console.log('- technical-seo.php');
console.log('- advanced.php');
