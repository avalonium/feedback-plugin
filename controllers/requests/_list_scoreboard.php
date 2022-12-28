<div class="scoreboard">
    <div data-control="toolbar">
        <div class="scoreboard-item control-chart" data-control="chart-pie">
            <ul>
                <li data-color="#95b753"><?= __('Open requests') ?> <span><?= $scoreboard->new_requests_count ?></span></li>
                <li data-color="#e5a91a"><?= __('Closed requests') ?> <span><?= $scoreboard->processed_requests_count ?></span></li>
                <li data-color="#cc3300"><?= __('Canceled requests') ?> <span><?= $scoreboard->canceled_requests_count ?></span></li>
            </ul>
        </div>
    </div>
</div>
