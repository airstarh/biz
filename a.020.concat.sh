#!/bin/bash

. ~/.bashrc

borg_fs_concat \
    ./app/Http/Controllers/AvailabilityController.php \
    ./app/Http/Controllers/HoldController.php \
    ./app/Models/Hold.php \
    ./app/Models/Slot.php \
    ./app/Services/SlotService.php \
    ./routes/web.php \
    ./database/factories/HoldFactory.php \
    ./database/factories/SlotFactory.php \
    ./database/migrations/2026_08_12_000000_create_slots_table.php \
    ./database/migrations/2026_08_12_000001_create_holds_table.php \
    ./tests/CreatesApplication.php \
    ./tests/TestCase.php \
    ./tests/Unit/Models/SlotTest.php \
    ./phpunit.xml \
    Dockerfile \
    ./.env \
    docker-compose.yml \
    > ./ai.prompts/codebase.txt
