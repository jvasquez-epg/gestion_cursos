document.getElementById('search').addEventListener('input', function(){
    const query = this.value.toLowerCase();
    document.querySelectorAll('.user-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(){
        const userId = this.dataset.id;
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: '¡No podrás revertir esto!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
        }).then(result => {
            if(result.isConfirmed){
                window.location = `${BASE_URL}admin/usuarios.php?role=${ROLE}&delete=${userId}`;
            }
        });
    });
});
