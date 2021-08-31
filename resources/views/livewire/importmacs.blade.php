<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      
    <div class="fixed inset-0 transition-opacity">
      <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
  
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​
  
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
      <form method="post" action="/importMacs" enctype="multipart/form-data" onsubmit="uploadbutton.disabled = true; return true;">
      @csrf
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="">
              <div class="mb-4">
                  Import to project:
                  <select wire:model="projectId" id="project_id" name="project_id">
                    @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->macs_count }})</option>
                    @endforeach
                  </select>
              </div>
              <div class="mb-4">
                  <label for="image" class="block text-gray-700 text-sm font-bold mb-2">CSV file (.csv):</label>
                  <input type="file" id="csv" name="csv" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              </div>
              <div class="mb-4">
                <b>Notes:</b><br><br>
                  <ul class="px-6 list-disc">
                    <li> First row is header consisting of `project_id` and `mac`.
                    <li> Upload file size limit configured in php.ini: {{ number_format($maxfilesize/1024/1024/1024, 1) }} GiB.
                    <li> Disk space available: {{ number_format( $freediskspace/1024/1024/1024, 1) }} GiB (should be at least twice the size of CSV).
                    <li> Be aware that after upload is finished it will process the CSV on the server, which may take some time.
                  </ul>
              </div>
        </div>
      </div>
  
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
          <button id="uploadbutton" type="submit" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
            Upload
          </button>
        </span>
        <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
            
          <button wire:click="closeImportModal()" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
            Cancel
          </button>
        </span>
        </form>
      </div>
        
    </div>
  </div>
</div>