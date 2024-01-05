<?php

use App\Models\WebArchiveTest;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Artisan;
use App\Events\WebEntityProcessed;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public Collection $categories;
    public Collection $websites;
    public string $category = '';
    public string $url = '';
    public string $selectedUrl = '';
    public string $message = '';
    protected $listeners = ['echo:new-entity,WebEntityProcessed' => 'notifyNewEntity'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->categories = $this->getCategories();
        $this->websites = collect();
    }

    protected function getCategories(): Collection
    {
        return WebArchiveTest::query()
            ->select(['category'])
            ->where('server', 'Charlotte')
            ->orderBy('category')
            ->distinct()
            ->get();
    }

    protected function getWebsites(string $category): Collection
    {
        return WebArchiveTest::query()
            ->select(['web_root', 'page_title'])
            ->where('server', 'Charlotte')
            ->where('category', $category)
            ->orderBy('web_root')
            ->get();
    }

    public function updatedUrl($url): void
    {
        $this->selectedUrl = $url;
        $this->categories = $this->getCategories();
        $this->websites = $this->getWebsites($this->category);
    }

    public function updatedCategory($category): void
    {
        $this->websites = $this->getWebsites($category);
        $this->categories = $this->getCategories();
    }


    /**
     * Update the profile information for the currently authenticated user.
     */
    public function launchArchiveProcess(): void
    {

        //Artisan::call('web:crawl', ['--url' => $this->url]);
        Log::debug('Sending...');

        event(new WebEntityProcessed);
    }

    public function notifyNewEntity()
    {
        Log::debug('Where are you?');
        $this->notify('You got it!');
    }

}; ?>

<section>
    <x-notifications />
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Website Archiver') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Choose a Category, then select a website.") }}
        </p>
    </header>

    <form wire:submit="launchArchiveProcess" class="mt-6 space-y-6">
        <div>
            <x-input-label for="categories" :value="__('Categories')"/>
            <select wire:model.change="category" class="form-control">
                <option value="" selected>Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category }}">
                        {{ ucfirst($category->category) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="websites" :value="__('Websites')"/>
            <select wire:model.change="url">
                <option value="" selected>Select Website</option>
                @foreach($websites as $url)
                    <option value="{{ $url->web_root }}">
                        {{ $url->page_title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            {{ $selectedUrl }}
            <iframe src="{{ $selectedUrl }}" style="width: 100%; border: 1px solid #eeeeee"></iframe>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Archive') }}</x-primary-button>
        </div>
    </form>
</section>
