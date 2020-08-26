<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Http\Resources\Contact as ResourcesContact;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{

    public function index()
    {
        $this->authorize('viewAny', Contact::class);
        return ResourcesContact::collection(request()->user()->contacts);
    }

    public function store()
    {
        $this->authorize('create', Contact::class);
        $contact = request()->user()->contacts()->create($this->validateData());

        return (new ResourcesContact($contact))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        return new ResourcesContact($contact);
    }

    public function update(Contact $contact)
    {
        $this->authorize('update', $contact);
        $contact->update($this->validateData());
    }
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);
        $contact->delete();
    }


    public function validateData()
    {
        return  request()->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'birthday' => ['required'],
            'company' => ['required'],
        ]);
    }
}
