<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component
{
    use WithPagination;

    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role = 'pelanggan';

    public $editingUserId;
    public $edit_name;
    public $edit_email;
    public $edit_password;
    public $edit_password_confirmation;
    public $edit_role;

    #[Computed]
    public function users()
    {
        return User::latest()->paginate(10);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,mekanik,pelanggan',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'role' => $this->role,
        ]);

        Flux::modal('create-user')->close();
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role']);

        session()->flash('success', 'User created successfully');
        $this->redirectRoute('user.index', navigate: true);
    }

    #[On('edit-user')]
    public function loadUser($id)
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_email = $user->email;
        $this->edit_role = $user->role;
        $this->edit_password = '';
        $this->edit_password_confirmation = '';

        Flux::modal('edit-user-modal')->show();
    }

    public function update()
    {
        $this->validate([
            'edit_name' => 'required|string|min:3|max:255',
            'edit_email' => 'required|email|max:255|unique:users,email,' . $this->editingUserId,
            'edit_password' => 'nullable|string|min:8|confirmed',
            'edit_role' => 'required|in:admin,mekanik,pelanggan',
        ]);

        $user = User::findOrFail($this->editingUserId);
        $data = [
            'name' => $this->edit_name,
            'email' => $this->edit_email,
            'role' => $this->edit_role,
        ];
        if ($this->edit_password) {
            $data['password'] = bcrypt($this->edit_password);
        }
        $user->update($data);

        Flux::modal('edit-user-modal')->close();
        $this->reset(['editingUserId', 'edit_name', 'edit_email', 'edit_password', 'edit_password_confirmation', 'edit_role']);

        session()->flash('success', 'User updated successfully');
        $this->redirectRoute('user.index', navigate: true);
    }

    #[On('confirm-delete')]
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account');
            return;
        }
        $user->delete();

        session()->flash('success', 'User deleted successfully');
        $this->redirectRoute('user.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->reset([
            'name', 'email', 'password', 'password_confirmation', 'role',
            'editingUserId', 'edit_name', 'edit_email', 'edit_password', 'edit_password_confirmation', 'edit_role',
        ]);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">User Management</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage users, roles, and access</flux:subheading>
    <flux:separator variant="subtle" />

    <flux:modal.trigger name="create-user">
        <flux:button variant="primary" icon="plus" color="primary">Add User</flux:button>
    </flux:modal.trigger>

    <x-flash-message />

    {{-- CREATE MODAL --}}
    <flux:modal name="create-user" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg">Create User</flux:heading>
                <flux:text>Add a new user to the system</flux:text>
            </div>

            <div class="space-y-6">
                <flux:input label="Name" placeholder="Full name" wire:model="name" />
                <flux:input label="Email" type="email" placeholder="email@example.com" wire:model="email" />
                <flux:input label="Password" type="password" wire:model="password" />
                <flux:input label="Confirm Password" type="password" wire:model="password_confirmation" />
                <flux:select label="Role" wire:model="role">
                    <flux:select.option value="pelanggan">Pelanggan</flux:select.option>
                    <flux:select.option value="mekanik">Mekanik</flux:select.option>
                    <flux:select.option value="admin">Admin</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- EDIT MODAL --}}
    <flux:modal name="edit-user-modal" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="update">
            <div class="space-y-2">
                <flux:heading size="lg">Edit User</flux:heading>
                <flux:text>Update user details</flux:text>
            </div>

            <div class="space-y-6">
                <flux:input label="Name" placeholder="Full name" wire:model="edit_name" />
                <flux:input label="Email" type="email" placeholder="email@example.com" wire:model="edit_email" />
                <flux:input label="New Password (leave empty to keep current)" type="password" wire:model="edit_password" />
                <flux:input label="Confirm New Password" type="password" wire:model="edit_password_confirmation" />
                <flux:select label="Role" wire:model="edit_role">
                    <flux:select.option value="pelanggan">Pelanggan</flux:select.option>
                    <flux:select.option value="mekanik">Mekanik</flux:select.option>
                    <flux:select.option value="admin">Admin</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Created At</flux:table.column>
                <flux:table.column class="text-right">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users as $index => $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="text-zinc-500">{{ $this->users->firstItem() + $index }}</flux:table.cell>
                        <flux:table.cell class="font-medium text-zinc-800 dark:text-white">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $roleColors = [
                                    'admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                    'mekanik' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    'pelanggan' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                ];
                                $color = $roleColors[$user->role] ?? 'bg-zinc-100 text-zinc-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">{{ $user->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="$dispatch('edit-user', { id: {{ $user->id }} })">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $user->id }}})" :disabled="$user->id === auth()->id()">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>
