/**
 * Convert a numeric amount into words in Nigerian Naira & Kobo.
 * Example: 85000000 -> "Eighty-Five Million Naira Only"
 */
export function convertNumberToWords(amount: number | string): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    if (isNaN(num) || num <= 0) return '';

    const units = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];
    const tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    function convertGroup(n: number): string {
        let str = '';
        if (n >= 100) {
            str += units[Math.floor(n / 100)] + ' Hundred ';
            n %= 100;
        }
        if (n >= 20) {
            str += tens[Math.floor(n / 10)] + (n % 10 > 0 ? '-' + units[n % 10] : '') + ' ';
        } else if (n > 0) {
            str += units[n] + ' ';
        }
        return str.trim();
    }

    const whole = Math.floor(num);
    const kobo = Math.round((num - whole) * 100);

    if (whole === 0 && kobo === 0) return 'Zero Naira Only';

    let wholeStr = '';
    let temp = whole;

    if (temp >= 1000000000) {
        wholeStr += convertGroup(Math.floor(temp / 1000000000)) + ' Billion ';
        temp %= 1000000000;
    }
    if (temp >= 1000000) {
        wholeStr += convertGroup(Math.floor(temp / 1000000)) + ' Million ';
        temp %= 1000000;
    }
    if (temp >= 1000) {
        wholeStr += convertGroup(Math.floor(temp / 1000)) + ' Thousand ';
        temp %= 1000;
    }
    if (temp > 0) {
        wholeStr += convertGroup(temp) + ' ';
    }

    wholeStr = wholeStr.trim() + ' Naira';

    if (kobo > 0) {
        wholeStr += ' and ' + convertGroup(kobo) + ' Kobo';
    } else {
        wholeStr += ' Only';
    }

    return wholeStr;
}
