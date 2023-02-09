<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilPDSelectedItemsBlockViewSettings
 */
class ilPDSelectedItemsBlockViewSettings implements ilPDSelectedItemsBlockConstants
{
    /** @var int[] */
    protected static array $availableViews = [
        self::VIEW_SELECTED_ITEMS,
        self::VIEW_RECOMMENDED_CONTENT,
        self::VIEW_MY_MEMBERSHIPS,
        self::VIEW_LEARNING_SEQUENCES,
        self::VIEW_MY_STUDYPROGRAMME,
    ];

    /** @var string[] */
    protected static array $viewNames = [
        self::VIEW_SELECTED_ITEMS => 'favourites',
        self::VIEW_MY_MEMBERSHIPS => 'memberships',
        self::VIEW_MY_STUDYPROGRAMME => 'studyprogramme',
        self::VIEW_RECOMMENDED_CONTENT => 'recommended_content',
        self::VIEW_LEARNING_SEQUENCES => 'learningsequences'
    ];

    /** @var string[] */
    protected static array $availablePresentations = [
        self::PRESENTATION_LIST,
        self::PRESENTATION_TILE
    ];

    /** @var string[] */
    protected static array $availableSortOptions = [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_START_DATE,
            self::SORT_BY_ALPHABET,
    ];

    protected static array $availableSortOptionsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_RECOMMENDED_CONTENT => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
            self::SORT_BY_START_DATE,
        ],
        self::VIEW_LEARNING_SEQUENCES => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_STUDYPROGRAMME => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
        ],
    ];

    /** @var array<int, string[]> */
    protected static array $availablePresentationsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_RECOMMENDED_CONTENT => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_LEARNING_SEQUENCES => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_STUDYPROGRAMME => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],

    ];

    protected ILIAS\Administration\Setting $settings;
    protected ilObjUser $actor;
    protected array $validViews = [];
    protected int $currentView = self::VIEW_SELECTED_ITEMS;
    protected string $currentSortOption = self::SORT_BY_LOCATION;
    protected string $currentPresentationOption = self::PRESENTATION_LIST;
    protected ILIAS\Dashboard\Access\DashboardAccess $access;

    public function __construct(
        ilObjUser $actor,
        int $view = self::VIEW_SELECTED_ITEMS,
        ILIAS\Administration\Setting $settings = null,
        ILIAS\Dashboard\Access\DashboardAccess $access = null
    ) {
        global $DIC;

        $this->settings = $settings ?? $DIC->settings();

        $this->actor = $actor;
        $this->currentView = $view;
        $this->access = $access ?? new ILIAS\Dashboard\Access\DashboardAccess();
    }

    public function getMembershipsView(): int
    {
        return self::VIEW_MY_MEMBERSHIPS;
    }

    public function getSelectedItemsView(): int
    {
        return self::VIEW_SELECTED_ITEMS;
    }

    public function getStudyProgrammeView(): int
    {
        return self::VIEW_MY_STUDYPROGRAMME;
    }

    public function getListPresentationMode(): string
    {
        return self::PRESENTATION_LIST;
    }

    public function getTilePresentationMode(): string
    {
        return self::PRESENTATION_TILE;
    }

    public function isMembershipsViewActive(): bool
    {
        return $this->currentView === $this->getMembershipsView();
    }

    public function isSelectedItemsViewActive(): bool
    {
        return $this->currentView === $this->getSelectedItemsView();
    }

    public function isStudyProgrammeViewActive(): bool
    {
        return $this->currentView === $this->getStudyProgrammeView();
    }

    public function isLearningSequenceViewActive(): bool
    {
        return $this->currentView === self::VIEW_LEARNING_SEQUENCES;
    }

    public function getSortByStartDateMode(): string
    {
        return self::SORT_BY_START_DATE;
    }

    public function getSortByLocationMode(): string
    {
        return self::SORT_BY_LOCATION;
    }

    public function getSortByTypeMode(): string
    {
        return self::SORT_BY_TYPE;
    }

    public function getSortByAlphabetMode(): string
    {
        return self::SORT_BY_ALPHABET;
    }

    public function getAvailableSortOptionsByView(int $view): array
    {
        return self::$availableSortOptionsByView[$view] ?? [];
    }

    public function getDefaultSortingByView(int $view): string
    {
        $sorting = $this->settings->get('pd_def_sort_by_view' . $view, self::SORT_BY_LOCATION);
        if (!in_array($sorting, $this->getAvailableSortOptionsByView($view), true)) {
            return $this->getAvailableSortOptionsByView($view)[0];
        }
        return $sorting;
    }

    /**
     * @return int[]
     */
    public function getPresentationViews(): array
    {
        return self::$availableViews;
    }

    /**
     * @return string[]
     */
    public function getAvailablePresentationsByView(int $view): array
    {
        return self::$availablePresentationsByView[$view];
    }

    public function storeViewSorting(int $view, string $type, array $active): void
    {
        if (!in_array($type, $active, true)) {
            $active[] = $type;
        }

        assert(in_array($type, $this->getAvailableSortOptionsByView($view), true));

        $this->settings->set('pd_def_sort_view' . $view, $type);
        $this->settings->set('pd_active_sort_view_' . $view, serialize($active));
    }

    public function getActiveSortingsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_sort_view_' . $view);
        if ($val === "" || $val === null) {
            $active_sortings = [];
        } else {
            $active_sortings = unserialize($val, ['allowed_classes' => false]);
        }
        return array_filter(
            $active_sortings,
            fn (string $sorting): bool =>
                in_array($sorting, $this->getAvailableSortOptionsByView($view), true)
        );
    }

    /**
     * @param string[]  $active
     */
    public function storeViewPresentation(int $view, string $default, array $active): void
    {
        if (!in_array($default, $active, true)) {
            $active[] = $default;
        }
        $this->settings->set('pd_def_pres_view_' . $view, $default);
        $this->settings->set('pd_active_pres_view_' . $view, serialize($active));
    }

    public function getDefaultPresentationByView(int $view): string
    {
        return $this->settings->get('pd_def_pres_view_' . $view, "list");
    }

    public function getActivePresentationsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_pres_view_' . $view, '');

        return (!$val)
            ? []
            : unserialize($val, ['allowed_classes' => false]);
    }

    public function enabledMemberships(): bool
    {
        return (int) $this->settings->get('disable_my_memberships', '0') === 0;
    }

    public function enabledSelectedItems(): bool
    {
        return (int) $this->settings->get('disable_my_offers', '0') === 0;
    }

    public function enableMemberships(bool $status): void
    {
        $this->settings->set('disable_my_memberships', (string) !$status);
    }

    public function enableSelectedItems(bool $status): void
    {
        $this->settings->set('disable_my_offers', (string) !$status);
    }

    public function allViewsEnabled(): bool
    {
        return $this->enabledMemberships() && $this->enabledSelectedItems();
    }

    protected function allViewsDisabled(): bool
    {
        return !$this->enabledMemberships() && !$this->enabledSelectedItems();
    }

    public function getDefaultView(): int
    {
        return (int) $this->settings->get('personal_items_default_view', (string) $this->getSelectedItemsView());
    }

    public function storeDefaultView(int $view): void
    {
        $this->settings->set('personal_items_default_view', (string) $view);
    }

    public function parse(): void
    {
        $this->validViews = self::$availableViews;

        $this->currentSortOption = $this->getEffectiveSortingMode();
        $this->currentPresentationOption = $this->getEffectivePresentationMode();
    }

    public function getEffectivePresentationMode(): string
    {
        $mode = $this->actor->getPref('pd_view_pres_' . $this->currentView);

        if (!in_array($mode, $this->getSelectablePresentationModes(), true)) {
            $mode = $this->getDefaultPresentationByView($this->currentView);
        }

        return $mode;
    }

    public function getEffectiveSortingMode(): string
    {
        $mode = $this->actor->getPref('pd_order_items_' . $this->currentView);

        if (!in_array($mode, $this->getSelectableSortingModes(), true)) {
            $mode = $this->getDefaultSortingByView($this->currentView);
        }

        return $mode;
    }

    /**
     * @return string[]
     */
    public function getSelectableSortingModes(): array
    {
        return array_intersect(
            $this->getActiveSortingsByView($this->currentView),
            $this->getAvailableSortOptionsByView($this->currentView)
        );
    }

    /**
     * @return string[]
     */
    public function getSelectablePresentationModes(): array
    {
        if (!$this->access->canChangePresentation($this->actor->getId())) {
            return [$this->getDefaultPresentationByView($this->currentView)];
        }
        return array_intersect(
            $this->getActivePresentationsByView($this->currentView),
            $this->getAvailablePresentationsByView($this->currentView)
        );
    }

    public function storeActorPresentationMode(string $presentationMode): void
    {
        if (in_array($presentationMode, $this->getSelectablePresentationModes())) {
            $this->actor->writePref(
                'pd_view_pres_' . $this->currentView,
                $presentationMode
            );
        }
    }

    public function storeActorSortingMode(string $sortingMode): void
    {
        if (in_array($sortingMode, $this->getSelectableSortingModes())) {
            $this->actor->writePref(
                'pd_order_items_' . $this->currentView,
                $sortingMode
            );
        }
    }

    public function getActor(): ilObjUser
    {
        return $this->actor;
    }

    public function getCurrentView(): int
    {
        return $this->currentView;
    }

    public function getCurrentSortOption(): string
    {
        return $this->currentSortOption;
    }

    public function isValidView(int $view): bool
    {
        return in_array($view, $this->validViews, true);
    }

    public function getDefaultSorting(): string
    {
        return $this->settings->get('dash_def_sort', $this->getSortByLocationMode());
    }

    public function isSortedByType(): bool
    {
        return $this->currentSortOption === $this->getSortByTypeMode();
    }

    public function isSortedByAlphabet(): bool
    {
        return $this->currentSortOption === $this->getSortByAlphabetMode();
    }

    public function isSortedByLocation(): bool
    {
        return $this->currentSortOption === $this->getSortByLocationMode();
    }

    public function isSortedByStartDate(): bool
    {
        return $this->currentSortOption === $this->getSortByStartDateMode();
    }

    public function isTilePresentation(): bool
    {
        return $this->currentPresentationOption === $this->getTilePresentationMode();
    }

    public function isListPresentation(): bool
    {
        return $this->currentPresentationOption === $this->getListPresentationMode();
    }

    public function enabledRecommendedContent(): bool
    {
        return (int) $this->settings->get('enable_recommended_content', '1') === 0;
    }

    public function enabledLearningSequences(): bool
    {
        return (int) $this->settings->get('enable_learning_sequences', '1') === 0;
    }

    public function enabledStudyProgrammes(): bool
    {
        return (int) $this->settings->get('enable_study_programmes', '1') === 0;
    }

    public function enableRecommendedContent(bool $status): void
    {
        $this->settings->set('enable_recommended_content', (string) !$status);
    }

    public function enableLearningSequences(bool $status): void
    {
        $this->settings->set('enable_learning_sequences', (string) !$status);
    }

    public function enableStudyProgrammes(bool $status): void
    {
        $this->settings->set('enable_study_programmes', (string) !$status);
    }

    public function getViewName(int $view): string
    {
        return self::$viewNames[$view];
    }
}
