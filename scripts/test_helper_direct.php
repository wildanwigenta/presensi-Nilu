<?php
// Test the TimeHelper directly with sample data

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\TimeHelper;

echo "=== TimeHelper Test Cases ===\n\n";

// Test 1: Normal case - same day, 8 hours
echo "Test 1 - Normal 9AM to 5PM:\n";
$result1 = TimeHelper::calculateWorkingHours('2026-05-23', '09:00:00', '2026-05-23', '17:00:00', true);
echo "Result: $result1\n\n";

// Test 2: Midnight crossover (no checkout date)
echo "Test 2 - Midnight crossover 10PM to 2AM (no checkout date):\n";
$result2 = TimeHelper::calculateWorkingHours('2026-05-23', '22:00:00', '', '02:00:00', true);
echo "Result: $result2\n\n";

// Test 3: Empty checkout time
echo "Test 3 - Empty checkout (should be 0 Jam 0 Menit):\n";
$result3 = TimeHelper::calculateWorkingHours('2026-05-23', '09:00:00', '', '', true);
echo "Result: $result3\n\n";

// Test 4: Both times are 00:00:00
echo "Test 4 - Both times 00:00:00 (should be 0 Jam 0 Menit):\n";
$result4 = TimeHelper::calculateWorkingHours('2026-05-23', '00:00:00', '2026-05-23', '00:00:00', true);
echo "Result: $result4\n\n";

// Test lateness
echo "Test 5 - Lateness check (10AM vs 9AM shift):\n";
$result5 = TimeHelper::calculateLateness('10:00:00', '09:00:00', true);
echo "Result: $result5\n\n";

echo "=== Tests Complete ===\n";
?>
