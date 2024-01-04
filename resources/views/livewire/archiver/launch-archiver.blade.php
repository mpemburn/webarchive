<?php

use App\Models\WebArchiveTest;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Artisan;

new class extends Component {
    public Collection $categories;
    public Collection $websites;
    public string $category = '';
    public string $url = '';
    public string $selectedUrl = '';
    public string $message = '';

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

    protected function getWebsites(string $category)
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

        $this->notify('archive-updated');
    }

}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Website Archiver') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Choose a Category, then select a URL.") }}
        </p>
    </header>

    <form wire:submit="launchArchiveProcess" class="mt-6 space-y-6">
        <div>
            <x-input-label for="categories" :value="__('Categories')"/>
            <select wire:model.change="category" class="form-control">
                <option value="" selected>Select category</option>
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
                <option value="" selected>Select URL</option>
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
